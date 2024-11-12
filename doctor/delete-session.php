<?php

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
    exit();
}

if ($_GET) {
    // Import database
    include("../connection.php");
    
    // Input validation and sanitization
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);  // Ensure the 'id' is a valid integer
    
    if ($id) {
        // SQL query with prepared statements to prevent SQL injection
        $stmt = $database->prepare("DELETE FROM schedule WHERE scheduleid = ?");
        $stmt->bind_param("i", $id);

        // Error handling for database operation
        if ($stmt->execute()) {
            header("location: schedule.php");
            exit();
        } else {
            // Log error for internal use and show a user-friendly message
            error_log("Database error: " . $stmt->error);  // Log the error details
            echo "An error occurred while processing your request. Please try again later.";
        }

        $stmt->close();
    } else {
        // If 'id' is invalid or missing
        echo "Invalid schedule ID. Please try again.";
    }
}
?>
