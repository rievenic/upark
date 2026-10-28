<?php
declare(strict_types=1);

require_once __DIR__ . "/config/Database.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function api_response(array $body, int $status = 200): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}

function api_error(string $message, int $status): void {
    api_response(["success" => false, "error" => $message], $status);
}

function read_json_body(): array {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw ?: "", true);
    if (!is_array($data)) {
        api_error("Request body must be a valid JSON object.", 400);
    }
    return $data;
}

function normalize_api_key(string $key): string {
    return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $key));
}

function reservation_is_active($status): bool {
    if ($status === null || trim((string) $status) === "") {
        return true;
    }
    return in_array(strtolower(trim((string) $status)), ["active", "reserved", "occupied", "pending"], true);
}

function require_positive_id($value, string $label): int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if ($id === false) {
        api_error("A valid {$label} is required.", 400);
    }
    return (int) $id;
}

function get_json_database(): PDO {
    // Database::getConnection() currently echoes connection errors; buffer that output
    // so it cannot corrupt the API's JSON response.
    ob_start();
    try {
        $database = new Database();
        $connection = $database->getConnection();
    } finally {
        ob_end_clean();
    }

    if (!$connection instanceof PDO) {
        api_error("Database connection is unavailable.", 500);
    }
    return $connection;
}

function get_events(PDO $db): void {
    $stmt = $db->query(
        "SELECT id, event_name, event_date, location, description, image_path
         FROM events
         ORDER BY event_date ASC"
    );
    api_response(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function get_slots(PDO $db): void {
    $status = isset($_GET["status"]) ? strtolower(trim((string) $_GET["status"])) : "";
    $allowed = ["available", "occupied", "reserved"];

    if ($status !== "" && !in_array($status, $allowed, true)) {
        api_error("Status must be available, occupied, or reserved.", 400);
    }

    if ($status === "") {
        $stmt = $db->query(
            "SELECT id, slot_number, zone, status
             FROM parking_slots
             ORDER BY zone ASC, CAST(slot_number AS UNSIGNED) ASC"
        );
    } else {
        $stmt = $db->prepare(
            "SELECT id, slot_number, zone, status
             FROM parking_slots
             WHERE status = :status
             ORDER BY zone ASC, CAST(slot_number AS UNSIGNED) ASC"
        );
        $stmt->execute([":status" => $status]);
    }

    api_response(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function get_summary(PDO $db): void {
    $stmt = $db->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(status = 'available'), 0) AS available,
                COALESCE(SUM(status = 'occupied'), 0) AS occupied,
                COALESCE(SUM(status = 'reserved'), 0) AS reserved
         FROM parking_slots"
    );
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    foreach (["total", "available", "occupied", "reserved"] as $field) {
        $summary[$field] = (int) ($summary[$field] ?? 0);
    }
    api_response(["success" => true, "data" => $summary]);
}

function create_reservation(PDO $db): void {
    $input = read_json_body();
    $driver = trim((string) ($input["driver_name"] ?? ""));
    $plate = trim((string) ($input["plate_number"] ?? ""));
    $slotId = require_positive_id($input["slot_id"] ?? null, "slot_id");
    $eventId = null;

    if (isset($input["event_id"]) && $input["event_id"] !== null && $input["event_id"] !== "") {
        $eventId = require_positive_id($input["event_id"], "event_id");
    }

    $providedKey = trim((string) ($input["custom_key"] ?? ""));
    $cleanKey = normalize_api_key($providedKey);

    if ($driver === "" || $plate === "") {
        api_error("driver_name and plate_number are required.", 400);
    }
    if (strlen($cleanKey) < 4) {
        api_error("custom_key must contain at least four letters or numbers.", 400);
    }

    try {
        $db->beginTransaction();

        $slotStmt = $db->prepare("SELECT status FROM parking_slots WHERE id = :id FOR UPDATE");
        $slotStmt->execute([":id" => $slotId]);
        $slot = $slotStmt->fetch(PDO::FETCH_ASSOC);
        if (!$slot || strtolower((string) $slot["status"]) !== "available") {
            $db->rollBack();
            api_error("The selected slot is not available.", 409);
        }

        $existingStmt = $db->prepare(
            "SELECT id FROM reservations
             WHERE slot_id = :slot_id
               AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending')
                    OR status = '' OR status IS NULL)
             LIMIT 1 FOR UPDATE"
        );
        $existingStmt->execute([":slot_id" => $slotId]);
        if ($existingStmt->fetch(PDO::FETCH_ASSOC)) {
            $db->rollBack();
            api_error("The selected slot already has an active reservation.", 409);
        }

        if ($eventId !== null) {
            $eventStmt = $db->prepare("SELECT id FROM events WHERE id = :id");
            $eventStmt->execute([":id" => $eventId]);
            if (!$eventStmt->fetchColumn()) {
                $db->rollBack();
                api_error("The selected event does not exist.", 400);
            }
        }

        $reservationCode = "UPARK-" . strtoupper(bin2hex(random_bytes(3)));
        $insertStmt = $db->prepare(
            "INSERT INTO reservations
                (reservation_code, slot_id, event_id, driver_name, plate_number, cancellation_key, status)
             VALUES
                (:code, :slot_id, :event_id, :driver, :plate, :cancel_key, 'reserved')"
        );
        $insertStmt->bindValue(":code", $reservationCode);
        $insertStmt->bindValue(":slot_id", $slotId, PDO::PARAM_INT);
        $insertStmt->bindValue(":event_id", $eventId, $eventId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $insertStmt->bindValue(":driver", $driver);
        $insertStmt->bindValue(":plate", $plate);
        $insertStmt->bindValue(":cancel_key", $cleanKey);
        $insertStmt->execute();

        $reservationId = (int) $db->lastInsertId();
        $updateSlot = $db->prepare("UPDATE parking_slots SET status = 'reserved' WHERE id = :id");
        $updateSlot->execute([":id" => $slotId]);
        $db->commit();

        api_response([
            "success" => true,
            "data" => [
                "id" => $reservationId,
                "reservation_code" => $reservationCode,
                "slot_id" => $slotId,
                "event_id" => $eventId,
                "driver_name" => $driver,
                "plate_number" => $plate,
                "status" => "reserved",
            ],
        ], 201);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function update_reservation(PDO $db, int $reservationId): void {
    $input = read_json_body();
    $providedKey = normalize_api_key(trim((string) ($input["cancellation_key"] ?? "")));
    $driver = trim((string) ($input["driver_name"] ?? ""));
    $plate = trim((string) ($input["plate_number"] ?? ""));

    if ($providedKey === "" || $driver === "" || $plate === "") {
        api_error("cancellation_key, driver_name, and plate_number are required.", 400);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare(
            "SELECT cancellation_key, status FROM reservations WHERE id = :id FOR UPDATE"
        );
        $stmt->execute([":id" => $reservationId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $db->rollBack();
            api_error("Reservation not found.", 404);
        }
        if (!reservation_is_active($reservation["status"])) {
            $db->rollBack();
            api_error("This reservation is no longer active.", 409);
        }
        if (!hash_equals(normalize_api_key((string) $reservation["cancellation_key"]), $providedKey)) {
            $db->rollBack();
            api_error("Invalid cancellation key.", 403);
        }

        $update = $db->prepare(
            "UPDATE reservations SET driver_name = :driver, plate_number = :plate WHERE id = :id"
        );
        $update->execute([":driver" => $driver, ":plate" => $plate, ":id" => $reservationId]);
        $db->commit();

        api_response([
            "success" => true,
            "data" => [
                "id" => $reservationId,
                "driver_name" => $driver,
                "plate_number" => $plate,
                "status" => (string) $reservation["status"],
            ],
        ]);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function cancel_reservation(PDO $db, int $reservationId): void {
    $input = read_json_body();
    $providedKey = normalize_api_key(trim((string) ($input["cancellation_key"] ?? "")));
    if ($providedKey === "") {
        api_error("cancellation_key is required.", 400);
    }

    $db->beginTransaction();
    try {
        // Lock the slot before the reservation, matching the create flow's lock order.
        $lookup = $db->prepare("SELECT slot_id FROM reservations WHERE id = :id");
        $lookup->execute([":id" => $reservationId]);
        $slotId = $lookup->fetchColumn();
        if ($slotId === false) {
            $db->rollBack();
            api_error("Reservation not found.", 404);
        }

        $slotLock = $db->prepare("SELECT id FROM parking_slots WHERE id = :id FOR UPDATE");
        $slotLock->execute([":id" => (int) $slotId]);

        $stmt = $db->prepare(
            "SELECT cancellation_key, status FROM reservations WHERE id = :id FOR UPDATE"
        );
        $stmt->execute([":id" => $reservationId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation || !reservation_is_active($reservation["status"])) {
            $db->rollBack();
            api_error("Reservation not found or no longer active.", 404);
        }
        if (!hash_equals(normalize_api_key((string) $reservation["cancellation_key"]), $providedKey)) {
            $db->rollBack();
            api_error("Invalid cancellation key.", 403);
        }

        $cancel = $db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = :id");
        $cancel->execute([":id" => $reservationId]);

        $remaining = $db->prepare(
            "SELECT COUNT(*) FROM reservations
             WHERE slot_id = :slot_id
               AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending')
                    OR status = '' OR status IS NULL)"
        );
        $remaining->execute([":slot_id" => (int) $slotId]);
        if ((int) $remaining->fetchColumn() === 0) {
            $release = $db->prepare("UPDATE parking_slots SET status = 'available' WHERE id = :id");
            $release->execute([":id" => (int) $slotId]);
        }

        $db->commit();
        api_response([
            "success" => true,
            "data" => ["id" => $reservationId, "status" => "cancelled"],
        ]);
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

$method = strtoupper($_SERVER["REQUEST_METHOD"] ?? "GET");
$resource = isset($_GET["resource"]) ? strtolower(trim((string) $_GET["resource"])) : "";

try {
    $db = get_json_database();

    if ($method === "GET" && $resource === "events") {
        get_events($db);
    }
    if ($method === "GET" && $resource === "slots") {
        get_slots($db);
    }
    if ($method === "GET" && $resource === "summary") {
        get_summary($db);
    }
    if ($resource === "reservations" && $method === "POST") {
        create_reservation($db);
    }

    if ($resource === "reservations" && in_array($method, ["PUT", "DELETE"], true)) {
        $reservationId = require_positive_id($_GET["id"] ?? null, "id");
        if ($method === "PUT") {
            update_reservation($db, $reservationId);
        }
        cancel_reservation($db, $reservationId);
    }

    if (!in_array($method, ["GET", "POST", "PUT", "DELETE"], true)) {
        header("Allow: GET, POST, PUT, DELETE");
        api_error("HTTP method not allowed.", 405);
    }
    api_error("Unknown resource or unsupported method.", 404);
} catch (Throwable $e) {
    error_log("U-PARK API error: " . $e->getMessage());
    api_error("The API could not complete the request.", 500);
}
