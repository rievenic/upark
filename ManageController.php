<?php
require_once "config/Database.php";
require_once "models/EventAndSlot.php";

class ManageController {
    private $db;
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new EventAndSlot($this->db);
    }

    public function events() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_event"])) {
            $event_name = trim($_POST["event_name"]);
            $event_date = $_POST["event_date"];
            $location = trim($_POST["location"]);
            $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
            $image_path = "default_event.jpg";

            if (isset($_FILES["event_photo"]) && $_FILES["event_photo"]["error"] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES["event_photo"]["name"], PATHINFO_EXTENSION);
                $new_filename = "event_" . time() . "." . $file_extension;

                if (move_uploaded_file($_FILES["event_photo"]["tmp_name"], $target_dir . $new_filename)) {
                    $image_path = $new_filename;
                }
            }

            if (!empty($event_name) && !empty($event_date) && !empty($location)) {
                $this->model->createEvent($event_name, $event_date, $location, $description, $image_path);
                header("Location: index.php?action=admin_events&msg=event_created");
                exit();
            }
        }

        $events = $this->model->readAllEvents();
        require_once "views/admin/events.php";
    }

    public function slots() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Guard PIN Protected Removal
            if (isset($_POST["clear_slot_protected"])) {
                $slot_id = $_POST["slot_id"];
                $pin = trim($_POST["guard_pin"]);
                
                if ($this->model->removeDriverByGuard($slot_id, $pin)) {
                    header("Location: index.php?action=slots&msg=slot_cleared");
                } else {
                    header("Location: index.php?action=slots&msg=invalid_pin");
                }
                exit();
            }

            // 1-Hour Overdue Direct Release Shortcut
            if (isset($_POST["clear_overdue_slot"])) {
                $slot_id = $_POST["slot_id"];
                if ($this->model->autoReleaseOverdueSlot($slot_id)) {
                    header("Location: index.php?action=slots&msg=slot_cleared");
                } else {
                    header("Location: index.php?action=slots&msg=not_overdue");
                }
                exit();
            }

            // Manual Status Update
            if (isset($_POST["update_status"])) {
                $slot_id = $_POST["slot_id"];
                $status = $_POST["status"];
                $this->model->updateSlotStatus($slot_id, $status);
                header("Location: index.php?action=slots&msg=status_updated");
                exit();
            }

            // Guard Edit Reservation
            if (isset($_POST["edit_reservation"])) {
                $reservation_id = $_POST["reservation_id"];
                $driver_name = trim($_POST["driver_name"]);
                $plate_number = trim($_POST["plate_number"]);

                if (!empty($reservation_id) && !empty($driver_name) && !empty($plate_number)) {
                    $this->model->updateReservationDetails($reservation_id, $driver_name, $plate_number);
                    header("Location: index.php?action=slots&msg=details_updated");
                    exit();
                }
            }
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $filter_event_id = isset($_GET['event_id']) ? trim($_GET['event_id']) : '';

        $slots = $this->model->readAllSlotsWithDetails($search, $filter_event_id);
        $summary = $this->model->getSlotSummary();
        $events = $this->model->readAllEvents();

        require_once "views/manage/slot.php";
    }

    public function logout() {
        unset($_SESSION["admin_logged_in"]);
        unset($_SESSION["admin_name"]);
        unset($_SESSION["admin_username"]);
        session_destroy();

        header("Location: index.php?action=login");
        exit();
    }
}