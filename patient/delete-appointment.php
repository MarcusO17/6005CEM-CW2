<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }
    
    
    // Process GET request to delete appointment
    if ($_GET) {
        // Validate and sanitize the 'id' parameter
        $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);
        
        if ($id) {
            // Import database connection
            include("../connection.php");
            
            // Use a prepared statement to delete the appointment securely
            $sql = "DELETE FROM appointment WHERE appoid = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $id);

            // Execute the query and check for success
            if ($stmt->execute()) {
                // Redirect to appointment page after successful deletion
                header("location: appointment.php");
                exit();
            } else {
                // Log error and display a user-friendly message
                error_log("Error deleting appointment: " . $stmt->error);
                echo "<p>Error: Unable to delete appointment. Please try again later.</p>";
            }

            $stmt->close();
        } else {
            echo "<p>Invalid appointment ID specified.</p>";
        }
    }


?>