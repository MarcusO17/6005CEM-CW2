<?php

session_start();

include('../session_handler.php');

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

include('../csrf_helper.php');
require_once("../connection.php");
require_once("../modules/Logger.php");
require_once("../modules/Analytics.php");

// Initialize Logger and Analytics with user email
$logger = Logger::getInstance($database);
$analytics = new Analytics($database, $useremail, 'p');

// Get user details
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
            setcookie(session_name(), '', time()-86400, '/');
        }
        session_unset();
        session_destroy();
        header('Location: ../login.php?csrf=true');
        exit();
    }

    $id = filter_var($_POST["id"], FILTER_VALIDATE_INT);
    $title = htmlspecialchars($_POST["title"], ENT_QUOTES, 'UTF-8');
    $docname = htmlspecialchars($_POST["docname"], ENT_QUOTES, 'UTF-8');
    
    if ($id) {
        try {
            // Start transaction
            $database->begin_transaction();

            // Get appointment details before deletion
            $stmt = $database->prepare("
                SELECT a.scheduleid, a.apponum, a.appodate, s.title, d.docname 
                FROM appointment a 
                JOIN schedule s ON a.scheduleid = s.scheduleid 
                JOIN doctor d ON s.docid = d.docid 
                WHERE a.appoid = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $appointment = $stmt->get_result()->fetch_assoc();
            
            if (!$appointment) {
                throw new Exception("Appointment not found");
            }

            // Delete the appointment
            $sql = "DELETE FROM appointment WHERE appoid = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                // Log the cancellation
                $logger->setUser($useremail, 'p')
                       ->log(
                           Logger::CATEGORY_APPOINTMENT,
                           'CANCEL_APPOINTMENT',
                           [
                               'appointment_id' => $id,
                               'schedule_id' => $appointment['scheduleid'],
                               'appointment_number' => $appointment['apponum'],
                               'appointment_date' => $appointment['appodate'],
                               'session_title' => $title,
                               'doctor_name' => $docname,
                               'patient_name' => $username
                           ],
                           Logger::LEVEL_INFO
                       );

                // Track the cancellation event
                $analytics->logUserEvent(
                    'APPOINTMENT',
                    'CANCEL',
                    'Appointment #' . $id,
                    1,
                    [
                        'user_id' => $userid,
                        'user_type' => 'p',
                        'appointment_id' => $id,
                        'schedule_id' => $appointment['scheduleid'],
                        'session_title' => $title,
                        'doctor_name' => $docname,
                        'cancel_date' => date('Y-m-d H:i:s')
                    ]
                );

                $database->commit();
                header("location: appointment.php?action=cancelled&id=" . $id);
                exit();
            } else {
                throw new Exception("Error deleting appointment");
            }
        } catch (Exception $e) {
            $database->rollback();
            
            // Log error
            $logger->setUser($useremail, 'p')
                   ->log(
                       Logger::CATEGORY_APPOINTMENT,
                       'CANCEL_ERROR',
                       [
                           'error' => $e->getMessage(),
                           'appointment_id' => $id,
                           'session_title' => $title,
                           'doctor_name' => $docname,
                           'patient_name' => $username
                       ],
                       Logger::LEVEL_ERROR
                   );

            header("location: appointment.php?action=error&message=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("location: appointment.php?action=error&message=Invalid appointment ID");
        exit();
    }
}
