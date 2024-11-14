<?php

session_start();

include('../session_handler.php');

// Check if the user is authenticated and authorized
if (isset($_SESSION["user"])) {
    if (empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit();
}

// Import database connection
include("../connection.php");
include('../csrf_helper.php');

// Prepare statement to fetch patient details
$sqlmain = "SELECT * FROM patient WHERE pemail = ?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$userid = htmlspecialchars($userfetch["pid"], ENT_QUOTES, 'UTF-8'); // Encoding applied
$username = htmlspecialchars($userfetch["pname"], ENT_QUOTES, 'UTF-8'); // Encoding applied

if ($_POST) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        header('Location: ../login.php?csrf=true');
        exit();
    }
    if (isset($_POST["booknow"])) {
        // Sanitize and validate inputs
        $apponum = filter_var($_POST["apponum"], FILTER_VALIDATE_INT);
        $scheduleid = filter_var($_POST["scheduleid"], FILTER_VALIDATE_INT);
        $date = htmlspecialchars($_POST["date"], ENT_QUOTES, 'UTF-8'); // Encoding applied

        // Check if inputs are valid
        if ($apponum && $scheduleid && $date) {
            // Use a prepared statement to insert the appointment
            $sql2 = "INSERT INTO appointment (pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)";
            $stmt2 = $database->prepare($sql2);
            $stmt2->bind_param("iiis", $userid, $apponum, $scheduleid, $date);
            if ($stmt2->execute()) {
                // Redirect on success
                header("location: appointment.php?action=booking-added&id=" . htmlspecialchars($apponum, ENT_QUOTES, 'UTF-8') . "&titleget=none");
                exit();
            } else {
                // Log error and show a user-friendly message
                error_log("Database error: " . $stmt2->error);
                echo "<p>Error: Unable to complete the booking. Please try again later.</p>";
            }
        } else {
            echo "<p>Invalid input. Please ensure all fields are filled in correctly.</p>";
        }
    }
}
?>
