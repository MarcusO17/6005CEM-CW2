<?php

include('../session_handler.php');

// Import database
include("../connection.php");

// Import EncryptionUtil
require "../utils/encryption-util.php";
include('../csrf_helper.php');
use function Utils\encrypt;    

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
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $nic = filter_var(trim($_POST['nic']), FILTER_SANITIZE_STRING);
    $oldemail = filter_var(trim($_POST["oldemail"]), FILTER_SANITIZE_EMAIL);
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $tele = filter_var(trim($_POST['Tele']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $id = $_POST['id00'];

    // Regex policies for validation
    $namePolicy = "/^[a-zA-Z\s'-]+$/"; // Letters, spaces, hyphens, apostrophes
    $addressPolicy = "/^[a-zA-Z0-9\s,.-]+$/"; // Letters, numbers, spaces, commas, periods, hyphens
    $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/"; // 1 digit, lowercase, uppercase, special character, 8-64 length

    // Validate Name
    if (!preg_match($namePolicy, $name)) {
        $error = '6'; // Error code for invalid name
    }
    // Validate Address
    elseif (!preg_match($addressPolicy, $address)) {
        $error = '7'; // Error code for invalid address
    }
    // Validate Password Policy
    elseif (!preg_match($passwordPolicy, $password)) {
        $error = '5'; // Error code for invalid password policy
    }
    // Check if Passwords Match
    elseif ($password !== $cpassword) {
        $error = '2'; // Error code for passwords not matching
    } else {
        // Check if the new email already exists for another patient
        $sqlmain = "SELECT patient.pid FROM patient INNER JOIN webuser ON patient.pemail = webuser.email WHERE webuser.email = ?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $id2 = ($result->num_rows == 1) ? $result->fetch_assoc()["pid"] : $id;

        if ($id2 != $id) {
            $error = '1'; // Email already exists for another patient
        } else {
            // Encrypt sensitive data
            $encrypted_nic = encrypt($nic);

            // Hash the password with Argon2ID
            $hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);

            // Update patient data
            $sql1 = "UPDATE patient SET pemail = ?, pname = ?, ppassword = ?, pnic = ?, ptel = ?, paddress = ? WHERE pid = ?";
            $stmt = $database->prepare($sql1);
            $stmt->bind_param("ssssssi", $email, $name, $hashedpassword, $encrypted_nic, $tele, $address, $id);
            $stmt->execute();

            // Update webuser data
            $sql2 = "UPDATE webuser SET email = ? WHERE email = ?";
            $stmt = $database->prepare($sql2);
            $stmt->bind_param("ss", $email, $oldemail);
            $stmt->execute();

            $error = '4'; // Success code

            // Update the session email to the new email
            // session_start();
            $_SESSION['user'] = $email;
        }
    }

} else {
    $error = '3'; // General error code if no POST data is received
}

header("location: settings.php?action=edit&error=" . urlencode($error) . "&id=" . urlencode($id));
?>

</body>
</html>
