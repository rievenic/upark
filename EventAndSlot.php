<?php
class EventAndSlot {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public static function normalizeKey($key) {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $key));
    }

    public static function generateNameBasedKey($driver_name) {
        $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $driver_name));
        $prefix = (strlen($cleanName) >= 6) ? substr($cleanName, 0, 6) : str_pad($cleanName, 6, 'X');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return $prefix . "-" . $random;
    }

    public function readAllEvents() {
        $query = "SELECT * FROM events ORDER BY event_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEvent($event_name, $event_date, $location, $description = '', $image_path = 'default_event.jpg') {
        $query = "INSERT INTO events (event_name, event_date, location, description, image_path) 
                  VALUES (:event_name, :event_date, :location, :description, :image_path)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":event_name", $event_name);
        $stmt->bindParam(":event_date", $event_date);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":image_path", $image_path);
        return $stmt->execute();
    }

    public function deleteEvent($id) {
        $query = "DELETE FROM events WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getAvailableSlots() {
        $query = "SELECT * FROM parking_slots WHERE status = 'available' ORDER BY zone ASC, slot_number ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlotSummary() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                    SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved
                  FROM parking_slots";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Release only the requested slot, and only when all its active reservations are overdue.
    public function autoReleaseOverdueSlot($slot_id, $minutes = 60) {
        try {
            $this->conn->beginTransaction();

            // Lock the slot first so concurrent booking attempts cannot claim it during release.
            $slotStmt = $this->conn->prepare(
                "SELECT id FROM parking_slots WHERE id = :slot_id FOR UPDATE"
            );
            $slotStmt->bindValue(":slot_id", $slot_id, PDO::PARAM_INT);
            $slotStmt->execute();

            if (!$slotStmt->fetchColumn()) {
                $this->conn->rollBack();
                return false;
            }

            $reservationStmt = $this->conn->prepare(
                "SELECT id,
                        TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= :mins AS is_overdue
                 FROM reservations
                 WHERE slot_id = :slot_id
                   AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending')
                        OR status = '' OR status IS NULL)
                 FOR UPDATE"
            );
            $reservationStmt->bindValue(":mins", $minutes, PDO::PARAM_INT);
            $reservationStmt->bindValue(":slot_id", $slot_id, PDO::PARAM_INT);
            $reservationStmt->execute();
            $reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($reservations)) {
                $this->conn->rollBack();
                return false;
            }

            foreach ($reservations as $reservation) {
                if (!(bool) $reservation["is_overdue"]) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $cancelStmt = $this->conn->prepare(
                "UPDATE reservations
                 SET status = 'cancelled'
                 WHERE slot_id = :slot_id
                   AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending')
                        OR status = '' OR status IS NULL)"
            );
            $cancelStmt->bindValue(":slot_id", $slot_id, PDO::PARAM_INT);
            $cancelStmt->execute();

            $releaseStmt = $this->conn->prepare(
                "UPDATE parking_slots SET status = 'available' WHERE id = :slot_id"
            );
            $releaseStmt->bindValue(":slot_id", $slot_id, PDO::PARAM_INT);
            $releaseStmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function createReservationWithCustomKey($slot_id, $event_id, $driver_name, $plate_number, $custom_key) {
        $cleanKey = self::normalizeKey($custom_key);
        $resCode = "UPARK-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $query = "INSERT INTO reservations (reservation_code, slot_id, event_id, driver_name, plate_number, cancellation_key, status) 
                  VALUES (:code, :slot_id, :event_id, :driver_name, :plate_number, :key, 'reserved')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $resCode);
        $stmt->bindParam(":slot_id", $slot_id);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":driver_name", $driver_name);
        $stmt->bindParam(":plate_number", $plate_number);
        $stmt->bindParam(":key", $cleanKey);

        if ($stmt->execute()) {
            $this->updateSlotStatus($slot_id, 'reserved');
            return true;
        }
        return false;
    }

    public function verifyKeyMatch($reservation_id, $provided_key) {
        $query = "SELECT cancellation_key FROM reservations WHERE id = :res_id AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending') OR status = '' OR status IS NULL)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":res_id", $reservation_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['cancellation_key'])) {
            return false;
        }

        $dbKey = self::normalizeKey($row['cancellation_key']);
        $userKey = self::normalizeKey($provided_key);

        return ($dbKey === $userKey);
    }

    public function clearSlotReservation($slot_id) {
        $query1 = "UPDATE reservations SET status = 'cancelled' WHERE slot_id = :slot_id AND (LOWER(status) IN ('active', 'reserved', 'occupied', 'pending') OR status = '' OR status IS NULL)";
        $stmt1 = $this->conn->prepare($query1);
        $stmt1->bindParam(":slot_id", $slot_id);
        $stmt1->execute();

        $query2 = "UPDATE parking_slots SET status = 'available' WHERE id = :slot_id";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bindParam(":slot_id", $slot_id);
        return $stmt2->execute();
    }

    public function cancelReservationWithKey($reservation_id, $slot_id, $provided_key) {
        if (!$this->verifyKeyMatch($reservation_id, $provided_key)) {
            return false;
        }
        return $this->clearSlotReservation($slot_id);
    }

    public function updateReservationWithKey($reservation_id, $driver_name, $plate_number, $provided_key) {
        if (!$this->verifyKeyMatch($reservation_id, $provided_key)) {
            return false;
        }

        $query = "UPDATE reservations SET driver_name = :driver, plate_number = :plate WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":driver", $driver_name);
        $stmt->bindParam(":plate", $plate_number);
        $stmt->bindParam(":id", $reservation_id);
        return $stmt->execute();
    }

    public function updateSlotStatus($slot_id, $status) {
        $query = "UPDATE parking_slots SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $slot_id);
        return $stmt->execute();
    }

    public function readAllSlotsWithDetails($search = '', $filter_event_id = '') {
        $query = "SELECT 
                    s.id AS slot_id, 
                    s.slot_number, 
                    s.zone, 
                    s.status,
                    r.id AS reservation_id,
                    r.driver_name, 
                    r.plate_number,
                    r.created_at AS booking_time,
                    e.id AS event_id,
                    COALESCE(e.event_name, 'General Campus Parking') AS event_name,
                    TIMESTAMPDIFF(MINUTE, r.created_at, NOW()) as elapsed_minutes,
                    CASE 
                        WHEN :filter_event IS NOT NULL AND :filter_event != '' AND r.event_id = :filter_event THEN 1
                        ELSE 2
                    END as event_priority
                  FROM parking_slots s
                  LEFT JOIN reservations r ON s.id = r.slot_id AND (LOWER(r.status) IN ('active', 'reserved', 'occupied', 'pending') OR r.status = '' OR r.status IS NULL)
                  LEFT JOIN events e ON r.event_id = e.id
                  WHERE (LOWER(r.driver_name) LIKE LOWER(:search) 
                      OR LOWER(r.plate_number) LIKE LOWER(:search) 
                      OR LOWER(s.slot_number) LIKE LOWER(:search) 
                      OR :search_raw = '')
                  ORDER BY event_priority ASC, s.zone ASC, CAST(s.slot_number AS UNSIGNED) ASC";

        $stmt = $this->conn->prepare($query);
        $cleanSearch = trim($search);
        $searchTerm = "%" . $cleanSearch . "%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->bindParam(":search_raw", $cleanSearch);
        $stmt->bindParam(":filter_event", $filter_event_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUserReservations($search) {
        $cleanSearch = trim($search);

        $query = "SELECT 
                    r.id AS reservation_id,
                    r.driver_name,
                    r.plate_number,
                    r.created_at,
                    r.status AS reservation_status,
                    s.id AS slot_id,
                    s.slot_number,
                    s.zone,
                    COALESCE(e.event_name, 'General Campus Parking') AS event_name
                  FROM reservations r
                  LEFT JOIN parking_slots s ON r.slot_id = s.id
                  LEFT JOIN events e ON r.event_id = e.id
                  WHERE (
                      REPLACE(LOWER(r.driver_name), ' ', '') LIKE REPLACE(LOWER(:search), ' ', '')
                      OR REPLACE(LOWER(r.plate_number), ' ', '') LIKE REPLACE(LOWER(:search), ' ', '')
                      OR LOWER(s.slot_number) LIKE LOWER(:search)
                  )
                  AND (LOWER(r.status) IN ('active', 'reserved', 'occupied', 'pending') OR r.status = '' OR r.status IS NULL)
                  ORDER BY r.created_at DESC";
                  
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%" . $cleanSearch . "%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
