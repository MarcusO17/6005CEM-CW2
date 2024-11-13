<?php

session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["medication"])) {

        // Input validation and sanitization
        $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $userid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);
        // Sanitize and encode text fields to prevent XSS if displayed
        $medication = htmlspecialchars(trim(filter_input(INPUT_POST, 'medication', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $dosage = htmlspecialchars(trim(filter_input(INPUT_POST, 'dosage', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $frequency = htmlspecialchars(trim(filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');
        $additional_notes = htmlspecialchars(trim(filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING)), ENT_QUOTES, 'UTF-8');

        // Check if required fields are provided and valid
        if ($appointment_id && $userid && $medication && $dosage && $frequency) {
            $sql = "INSERT INTO prescription (pid, appointment_id, medication, dosage, frequency, additional_notes) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("iissss", $userid, $appointment_id, $medication, $dosage, $frequency, $additional_notes);
            
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
