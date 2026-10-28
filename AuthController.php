<?php
require_once "config/Database.php";

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Admin Registration with Secret Key Validation
    public function register() {
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $fullname = isset($_POST["fullname"]) ? trim($_POST["fullname"]) : "";
            $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
            $password = isset($_POST["password"]) ? $_POST["password"] : "";
            $secret_key = isset($_POST["secret_key"]) ? trim($_POST["secret_key"]) : "";

            // Fetch the passkey safely
            $expected_key = defined('Database::ADMIN_SECRET_KEY') ? Database::ADMIN_SECRET_KEY : 'rievenic_admin';

            // Secret key validation - DOES NOT print or leak the key
            if ($secret_key !== $expected_key) {
                $error = "Invalid Security Passkey! Administrative authorization failed.";
            } elseif (empty($fullname) || empty($username) || empty($password)) {
                $error = "Please fill in all required fields.";
            } else {
                try {
                    // Check if username already exists
                    $checkStmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
                    $checkStmt->bindParam(":username", $username);
                    $checkStmt->execute();

                    if ($checkStmt->rowCount() > 0) {
                        $error = "Username is already taken. Please choose another.";
                    } else {
                        // Secure Password Hashing
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        $stmt = $this->db->prepare("INSERT INTO users (fullname, username, password, role) VALUES (:fullname, :username, :password, 'admin')");
                        $stmt->bindParam(":fullname", $fullname);
                        $stmt->bindParam(":username", $username);
                        $stmt->bindParam(":password", $hashed_password);

                        if ($stmt->execute()) {
                            header("Location: index.php?action=login&msg=registered");
                            exit();
                        } else {
                            $error = "Registration failed. Please try again.";
                        }
                    }
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        }

        require_once "views/auth/register.php";
    }

    // Admin Login Handler
    public function login() {
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
            $password = isset($_POST["password"]) ? $_POST["password"] : "";

            if (!empty($username) && !empty($password)) {
                try {
                    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username AND role = 'admin'");
                    $stmt->bindParam(":username", $username);
                    $stmt->execute();
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user["password"])) {
                        $_SESSION["admin_logged_in"] = true;
                        $_SESSION["admin_name"] = isset($user["fullname"]) ? $user["fullname"] : $user["username"];
                        $_SESSION["admin_username"] = $user["username"];

                        header("Location: index.php?action=admin_events");
                        exit();
                    } else {
                        $error = "Invalid username or password.";
                    }
                } catch (PDOException $e) {
                    $error = "Database Error: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields.";
            }
        }

        require_once "views/auth/login.php";
    }

    // Logout Handler
    public function logout() {
        unset($_SESSION["admin_logged_in"]);
        unset($_SESSION["admin_name"]);
        unset($_SESSION["admin_username"]);
        session_destroy();

        header("Location: index.php?action=login");
        exit();
    }
}