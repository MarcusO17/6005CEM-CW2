<?php

include('../session_handler.php');
include("../connection.php");
require "../utils/encryption-util.php";
include('../csrf_helper.php');
use function Utils\encrypt;

session_start();

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

    // Sanitize inputs
    $id = filter_var($_POST['id00'], FILTER_VALIDATE_INT);
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $nic = filter_var(trim($_POST['nic']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $tele = filter_var(trim($_POST['Tele']), FILTER_SANITIZE_STRING);
    $specialty = filter_var(trim($_POST['spec']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Validation policies
    $namePolicy = "/^[a-zA-Z\s'-]+$/"; // Letters, spaces, hyphens, apostrophes
    $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/"; // 1 digit, lowercase, uppercase, special character, 8-64 length

    // Name Validation
    if (!preg_match($namePolicy, $name)) {
        $error = '6'; // Error code for invalid name
    }
    // Password Policy Validation
    elseif (!preg_match($passwordPolicy, $password)) {
        $error = '5'; // Error code for invalid password policy
    }
    // Check if Passwords Match
    elseif ($password !== $cpassword) {
        $error = '2'; // Error code for passwords not matching
    } else {
        // Check if the new email already exists for another doctor
        $sqlmain = "SELECT docid FROM doctor WHERE docemail = ? AND docid != ?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = '1'; // Email already exists for another doctor
        } else {
            // Encrypt sensitive data
            $encrypted_nic = encrypt($nic);

            // Hash the password with Argon2ID
            $hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);

            // Update doctor data
            $sql = "UPDATE doctor SET docname = ?, docemail = ?, docnic = ?, doctel = ?, specialties = ?, docpassword = ? WHERE docid = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ssssssi", $name, $email, $encrypted_nic, $tele, $specialty, $hashedpassword, $id);
            $stmt->execute();

            // Update email in the webuser table
            $sql = "UPDATE webuser SET email = ? WHERE email = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ss", $email, $_SESSION['user']);
            $stmt->execute();

            $error = '4'; // Success code

            // Update the session email to the new email
            $_SESSION['user'] = $email;
        }
    }
} else {
    $error = '3'; // General error code if no POST data is received
}

    header("Location: settings.php?action=edit&error=" . urlencode($error) . "&id=" . urlencode($id));
    exit();
?>
