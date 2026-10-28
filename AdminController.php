<?php
require_once "config/Database.php";
require_once "models/EventAndSlot.php";

class AdminController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new EventAndSlot($this->db);
    }

    // Admin Panel: Post New Event & View Created Events
    public function manageEvents() {
        $msg = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_event"])) {
            $event_name = trim($_POST["event_name"]);
            $event_date = $_POST["event_date"];
            $location = trim($_POST["location"]);
            $description = trim($_POST["description"]);
            
            // Image Upload Handling
            $image_path = "default_event.jpg";
            if (isset($_FILES["event_photo"]) && $_FILES["event_photo"]["error"] == 0) {
                $target_dir = "uploads/";
                $file_extension = pathinfo($_FILES["event_photo"]["name"], PATHINFO_EXTENSION);
                $new_filename = "event_" . time() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;

                if (move_uploaded_file($_FILES["event_photo"]["tmp_name"], $target_file)) {
                    $image_path = $new_filename;
                }
            }

            if (!empty($event_name) && !empty($event_date) && !empty($location)) {
                $this->model->createEvent($event_name, $event_date, $location, $description, $image_path);
                header("Location: index.php?action=admin_events&msg=created");
                exit();
            }
        }

        $events = $this->model->readAllEvents();
        require_once "views/admin/events.php";
    }
}