<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/Database.php";
require_once "models/EventAndSlot.php";

require_once "controllers/ManageController.php";
require_once "controllers/AuthController.php";
require_once "controllers/UserController.php";

$action = isset($_GET['action']) ? $_GET['action'] : 'user_portal';

$manageController = new ManageController();
$authController = new AuthController();
$userController = new UserController();

function requireAdminLogin() {
    if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
        header("Location: index.php?action=login");
        exit();
    }
}

switch ($action) {
    case 'login':
        $authController->login();
        break;

    case 'register':
        $authController->register();
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'admin_events':
        requireAdminLogin();
        $manageController->events();
        break;

    case 'slots':
        requireAdminLogin();
        $manageController->slots();
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once "controllers/ReservationController.php";
            $reservationController = new ReservationController();
            $reservationController->create();
            break;
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $userController->createReservation();
        } else {
            require_once "controllers/ReservationController.php";
            $reservationController = new ReservationController();
            $reservationController->create();
        }
        break;

    case 'cancel_user_reservation':
        $userController->cancelReservation();
        break;

    case 'edit_user_reservation':
        $userController->editReservation();
        break;

    case 'user_portal':
    default:
        $userController->portal();
        break;
}
