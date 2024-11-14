<?php

session_start();

include('../session_handler.php');

if (isset($_SESSION["user"])) {
    if ($_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = htmlspecialchars($_SESSION["user"], ENT_QUOTES, 'UTF-8'); // Output encoding for safety
    }
} else {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// import EncryptionUtil
require "../utils/encryption-util.php";
use function Utils\encrypt;


if ($_POST) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-86400, '/');
        }

        session_unset();
        session_destroy();
        
        header('Location: ../login.php?csrf=true');
        exit();
    }

    if (isset($_POST["medication"])) {

        // Input validation and sanitization
        $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $userid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);
        // Sanitize and encode text fields to prevent XSS if displayed
        $medication = htmlspecialchars(trim(filter_input(INPUT_POST, 'medication', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $dosage = htmlspecialchars(trim(filter_input(INPUT_POST, 'dosage', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $frequency = htmlspecialchars(trim(filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $additional_notes = htmlspecialchars(trim(filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');

        // Encrypt sensitive data
        $encrypted_medication = encrypt($medication);
        $encrypted_dosage = encrypt($dosage);
        $encrypted_frequency = encrypt($frequency);
        $encrypted_additional_notes = encrypt($additional_notes);

        // Check if required fields are provided and valid
        if ($appointment_id && $userid && $medication && $dosage && $frequency) {
            $sql = "INSERT INTO prescription (pid, appointment_id, medication, dosage, frequency, additional_notes) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("iissss", $userid, $appointment_id, $encrypted_medication, $encrypted_dosage, $encrypted_frequency, $encrypted_additional_notes);
            
            if ($stmt->execute()) {
                header("location: appointment.php?action=prescription-added&id=" . urlencode($appointment_id) . "&titleget=none");
                exit();
            } else {
                // Log error (for internal use) and show a user-friendly message
                error_log("Database error: " . $stmt->error); // Logs error without exposing details
                echo "An error occurred while processing your request. Please try again later.";
            }
        } else {
            echo "Please provide valid information for all required fields.";
        }
    }
}
?>
