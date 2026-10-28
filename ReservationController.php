<?php
require_once "config/Database.php";
require_once "models/Reservation.php";

class ReservationController {
    private $db;
    private $reservationModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reservationModel = new Reservation($this->db);
    }

    // Display the list of all reservations
    public function index() {
        $stmt = $this->reservationModel->readAll();
        $reservations = $stmt->fetchAll();
        require_once "views/reservations/index.php";
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

    // Handle creation form display and submission
    public function create() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $driver_name = trim($_POST["driver_name"]);
            $plate_number = trim($_POST["plate_number"]);
            $event_id = $_POST["event_id"];
            $slot_id = $_POST["slot_id"];

            if (!empty($driver_name) && !empty($plate_number) && !empty($event_id) && !empty($slot_id)) {
                $success = $this->reservationModel->create($driver_name, $plate_number, $event_id, $slot_id);
                if ($success) {
                    // Redirect back to main list view after successful creation
                    header("Location: index.php?action=index&msg=success");
                    exit();
                } else {
                    $error = "The selected parking slot is currently unavailable or already reserved.";
                }
            } else {
                $error = "Please fill in all required fields.";
            }
        }
        
        // Fetch available slots and active events for dropdown selects in the view
        $slots = $this->db->query("SELECT id, slot_number FROM parking_slots WHERE status = 'available'")->fetchAll();
        $events = $this->db->query("SELECT id, event_name, location FROM events ORDER BY event_date ASC")->fetchAll();
        $googleMapsConfig = $this->loadGoogleMapsConfig();
        $googleMapsApiKey = $googleMapsConfig['browser_key'];
        $googleMapsBrowserKey = $googleMapsApiKey;
        $googleMapsConfigured = $googleMapsConfig['configured'];
        
        require_once "views/reservations/create.php";
    }
}
