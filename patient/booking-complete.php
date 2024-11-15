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

// Import database connection and required modules
include("../connection.php");
include('../csrf_helper.php');
require_once("../modules/Logger.php");
require_once("../modules/Analytics.php");

// Initialize Logger and Analytics
$logger = Logger::getInstance($database);
$analytics = new Analytics($database, $useremail, 'p');

// Prepare statement to fetch patient details
$sqlmain = "SELECT * FROM patient WHERE pemail = ?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$userid = htmlspecialchars($userfetch["pid"], ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars($userfetch["pname"], ENT_QUOTES, 'UTF-8');

if ($_POST) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 86400, '/');
        }
        session_unset();
        session_destroy();
        header('Location: ../login.php?csrf=true');
        exit();
    }

    if (isset($_POST["booknow"])) {
        // Sanitize and validate inputs
        $apponum = filter_var($_POST["apponum"], FILTER_VALIDATE_INT);
        $scheduleid = filter_var($_POST["scheduleid"], FILTER_VALIDATE_INT);
        $date = htmlspecialchars($_POST["date"], ENT_QUOTES, 'UTF-8');

        // Check if inputs are valid
        if ($apponum && $scheduleid && $date) {
            try {
                // Start transaction
                $database->begin_transaction();

                // Get schedule details for logging
                $schedule_query = "SELECT title, docid FROM schedule WHERE scheduleid = ?";
                $stmt = $database->prepare($schedule_query);
                $stmt->bind_param("i", $scheduleid);
                $stmt->execute();
                $schedule_result = $stmt->get_result();
                $schedule_data = $schedule_result->fetch_assoc();

                // Insert appointment
                $sql2 = "INSERT INTO appointment (pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)";
                $stmt2 = $database->prepare($sql2);
                $stmt2->bind_param("iiis", $userid, $apponum, $scheduleid, $date);

                if ($stmt2->execute()) {
                    // Log the appointment booking
                    $logger->setUser($useremail, 'p')
                        ->log(
                            Logger::CATEGORY_APPOINTMENT,
                            'BOOK',
                            [
                                'appointment_number' => $apponum,
                                'schedule_id' => $scheduleid,
                                'schedule_title' => $schedule_data['title'],
                                'booking_date' => $date
                            ],
                            Logger::LEVEL_INFO
                        );

                    // Track the booking event
                    $analytics->logUserEvent(
                        'APPOINTMENT',
                        'BOOK',
                        'Appointment #' . $apponum,
                        1,
                        [
                            'user_id' => $userid,
                            'user_type' => 'p',
                            'schedule_id' => $scheduleid,
                            'doctor_id' => $schedule_data['docid'],
                            'appointment_date' => $date
                        ]
                    );

                    // Track page view
                    $analytics->trackPageView(
                        '/patient/booking-complete.php',
                        'Appointment Booking Confirmation',
                        $useremail,
                        'p'
                    );

                    $database->commit();
                    header("location: appointment.php?action=booking-added&id=" . $apponum . "&titleget=none");
                    exit();
                } else {
                    $database->rollback();
                    error_log("Failed to insert appointment");
                    throw new Exception("Failed to insert appointment");
                }
            } catch (Exception $e) {
                $database->rollback();
                error_log("Failed to commit transaction");
                error_log("Failed to insert appointment: " . $e->getMessage());
                // Log the error
                $logger->setUser($useremail, 'p')
                    ->log(
                        Logger::CATEGORY_APPOINTMENT,
                        'BOOK_ERROR',
                        [
                            'error_message' => $e->getMessage(),
                            'schedule_id' => $scheduleid,
                            'attempted_date' => $date
                        ],
                        Logger::LEVEL_ERROR
                    );

                echo "<p>Error: Unable to complete the booking. Please try again later.</p>";
            }
        } else {
            // Log validation error
            $logger->setUser($useremail, 'p')
                ->log(
                    Logger::CATEGORY_APPOINTMENT,
                    'BOOK_VALIDATION_ERROR',
                    [
                        'apponum' => $apponum,
                        'scheduleid' => $scheduleid,
                        'date' => $date
                    ],
                    Logger::LEVEL_WARNING
                );

            echo "<p>Invalid input. Please ensure all fields are filled in correctly.</p>";
        }
    }
}
