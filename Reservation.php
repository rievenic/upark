<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all reservation records and handle 1-hour grace period auto-expiration
    public function readAll() {
        // Auto-expire active reservations where current time exceeds the grace period
        $expireQuery = "UPDATE " . $this->table_name . " 
                        SET status = 'expired' 
                        WHERE status = 'active' AND expires_at < NOW()";
        $this->conn->exec($expireQuery);

        // Retrieve reservations with slot and event details
        $query = "SELECT r.id, r.reservation_code, r.driver_name, r.plate_number, 
                         s.slot_number, e.event_name, r.status, r.created_at, r.expires_at 
                  FROM " . $this->table_name . " r
                  JOIN parking_slots s ON r.slot_id = s.id
                  JOIN events e ON r.event_id = e.id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create a new reservation with double-booking prevention
    public function create($driver_name, $plate_number, $event_id, $slot_id) {
        // Check if the slot is already actively reserved
        $checkQuery = "SELECT id FROM " . $this->table_name . " 
                       WHERE slot_id = :slot_id AND status = 'active'";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":slot_id", $slot_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            return false; // Slot is occupied or active
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (reservation_code, driver_name, plate_number, event_id, slot_id, expires_at) 
                  VALUES (:code, :driver_name, :plate_number, :event_id, :slot_id, DATE_ADD(NOW(), INTERVAL 1 HOUR))";

        $stmt = $this->conn->prepare($query);
        $code = "UPARK-" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":driver_name", $driver_name);
        $stmt->bindParam(":plate_number", $plate_number);
        $stmt->bindParam(":event_id", $event_id);
        $stmt->bindParam(":slot_id", $slot_id);

        if ($stmt->execute()) {
            // Mark slot status as reserved
            $updateSlot = "UPDATE parking_slots SET status = 'reserved' WHERE id = :slot_id";
            $updateStmt = $this->conn->prepare($updateSlot);
            $updateStmt->bindParam(":slot_id", $slot_id);
            $updateStmt->execute();
            return true;
        }
        return false;
    }
}