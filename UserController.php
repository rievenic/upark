<?php
require_once "config/Database.php";
require_once "models/EventAndSlot.php";

class UserController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->model = new EventAndSlot($this->db);
    }

    private function loadGoogleMapsConfig() {
        $configPath = __DIR__ . '/../config/google_maps.php';
        $config = [];

        if (file_exists($configPath)) {
            $config = require $configPath;
        }

        if (!is_array($config)) {
            $config = [];
        }

        $browserKey = getenv('GOOGLE_MAPS_BROWSER_KEY')
            ?: getenv('GOOGLE_MAPS_API_KEY')
            ?: ($config['browser_key'] ?? $config['api_key'] ?? '');

        return [
            'browser_key' => (string) $browserKey,
            'configured' => !empty($browserKey),
        ];
    }

    public function portal() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $events = $this->model->readAllEvents();
        $available_slots = $this->model->getAvailableSlots();
        $summary = $this->model->getSlotSummary();
        $googleMapsConfig = $this->loadGoogleMapsConfig();
        $googleMapsApiKey = $googleMapsConfig['browser_key'];
        $googleMapsBrowserKey = $googleMapsApiKey;
        $googleMapsConfigured = $googleMapsConfig['configured'];
        
        $user_reservations = [];
        if ($search !== '') {
            $user_reservations = $this->model->searchUserReservations($search);
        }

        require_once "views/user/portal.php";
    }

    public function createReservation() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $driver_name = trim($_POST["driver_name"]);
            $plate_number = trim($_POST["plate_number"]);
            $event_id = !empty($_POST["event_id"]) ? $_POST["event_id"] : null;
            $slot_id = !empty($_POST["slot_id"]) ? $_POST["slot_id"] : null;
            $custom_key = isset($_POST["custom_key"]) ? trim($_POST["custom_key"]) : '';

            if (empty($custom_key)) {
                $custom_key = EventAndSlot::generateNameBasedKey($driver_name);
            }

            if (!empty($driver_name) && !empty($plate_number) && $slot_id) {
                if ($this->model->createReservationWithCustomKey($slot_id, $event_id, $driver_name, $plate_number, $custom_key)) {
                    $cleanKey = EventAndSlot::normalizeKey($custom_key);
                    header("Location: index.php?action=user_portal&msg=reserved&key=" . urlencode($cleanKey));
                    exit();
                }
            }
        }
        header("Location: index.php?action=user_portal&msg=error");
        exit();
    }

    public function cancelReservation() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $reservation_id = isset($_POST["reservation_id"]) ? $_POST["reservation_id"] : null;
            $slot_id = isset($_POST["slot_id"]) ? $_POST["slot_id"] : null;
            $provided_key = isset($_POST["cancellation_key"]) ? trim($_POST["cancellation_key"]) : '';

            if ($reservation_id && $slot_id && !empty($provided_key)) {
                if ($this->model->cancelReservationWithKey($reservation_id, $slot_id, $provided_key)) {
                    header("Location: index.php?action=user_portal&msg=cancelled");
                    exit();
                }
            }
            header("Location: index.php?action=user_portal&msg=invalid_key");
            exit();
        }
    }

    public function editReservation() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $reservation_id = isset($_POST["reservation_id"]) ? $_POST["reservation_id"] : null;
            $driver_name = isset($_POST["driver_name"]) ? trim($_POST["driver_name"]) : '';
            $plate_number = isset($_POST["plate_number"]) ? trim($_POST["plate_number"]) : '';
            $provided_key = isset($_POST["cancellation_key"]) ? trim($_POST["cancellation_key"]) : '';

            if ($reservation_id && !empty($driver_name) && !empty($plate_number) && !empty($provided_key)) {
                if ($this->model->updateReservationWithKey($reservation_id, $driver_name, $plate_number, $provided_key)) {
                    // Redirect back while keeping search filter active if driver_name was searched
                    header("Location: index.php?action=user_portal&msg=updated&search=" . urlencode($driver_name));
                    exit();
                }
            }
            header("Location: index.php?action=user_portal&msg=invalid_key");
            exit();
        }
    }
}
