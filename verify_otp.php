<?php
require "utils/encryption-util.php";
use function Utils\encrypt;
include("connection.php");

session_start();
$otp = implode('', $_POST['otp']);

if ($_SESSION['otp'] == NULL) {
    echo ("OTP not generated.");
}

$generatedOtp = $_SESSION['otp'];
$expiryTime = $_SESSION['expiryTime'];


$currentTime = time();
if ($otp == $generatedOtp && $currentTime <= $expiryTime) {
    echo "OTP verified successfully!";

    unset($_SESSION['otp']);
    unset($_SESSION['expiryTime']);
    unset($_SESSION['otp_error_message']);
    if ($_SESSION['usertype'] == 'p') {
        session_regenerate_id(true);

        //   Patient dashbord
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        include('session_handler.php');
        resetAccountLock($database, $_SESSION['user']);
        header('location: patient/index.php');
    }
    if ($_SESSION['usertype'] == 'd') {
        session_regenerate_id(true);

        //   doctor dashbord
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        include('session_handler.php');
        resetAccountLock($database, $_SESSION['user']);
        header('location: doctor/index.php');
    }
    if($_SESSION['usertype'] == 'pnew'){

        $email = $_SESSION['credentials']['email'];
        $password = $_SESSION['credentials']['password'];
        $tele =  $_SESSION['credentials']['tele'];
        $fname=$_SESSION['personal']['fname'];
        $lname=$_SESSION['personal']['lname'];
        $name=$fname." ".$lname;
        $address=$_SESSION['personal']['address'];
        $nic=$_SESSION['personal']['nic'];
        $dob=$_SESSION['personal']['dob'];
        // Encrypt sensitive data
        $encrypted_nic = encrypt($nic);


        // Insert data into the patient table
        $stmt = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $email, $name, $password, $address, $encrypted_nic, $dob, $tele);
        $stmt->execute();

        // Insert data into the webuser table
        $stmt = $database->prepare("INSERT INTO webuser (email, usertype, attempts, last_recorded_attempt, end_lockout) VALUES (?, 'p', 0, NULL, NULL)");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $_SESSION["user"]=$email;
        $_SESSION["usertype"]="p";
        $_SESSION["username"]=$fname;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        include('session_handler.php');

        header('Location: patient/index.php');
    }
} else {
    if ($currentTime > $expiryTime) {
        unset($_SESSION['otp']);
        unset($_SESSION['expiryTime']);
        $_SESSION['otp_error_message'] = "OTP expired. Please request a new one.";
        header('location: login.php');
    } else {
        $_SESSION['otp_error_message'] = "Invalid OTP. Please try again.";
        header('location: login.php');
    }
}

function resetAccountLock($database, $email)
{
    $sql1 =  " UPDATE webuser SET attempts=NULL, last_recorded_attempt=NULL, end_lockout=NULL where email='$email';";
    $database->query($sql1);
}
