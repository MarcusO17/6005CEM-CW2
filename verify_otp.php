<?php

session_start();
$otp = implode('', $_POST['otp']);

if ($_SESSION['otp'] == NULL) {
    echo("OTP not generated.");
}

$generatedOtp = $_SESSION['otp'];
$expiryTime = $_SESSION['expiryTime'];

$currentTime = time();
if ($otp == $generatedOtp && $currentTime <= $expiryTime) {
    echo "OTP verified successfully!";
   
    unset($_SESSION['otp']);
    unset($_SESSION['expiryTime']);
    unset($_SESSION['otp_error_message']);
    if ($_POST['user_id']=='p'){
        $_SESSION['usertype']='p';
        header('location: patient/index.php');
    }
    if ($_POST['user_id']=='d'){
        $_SESSION['usertype']='d';
        header('location: doctor/index.php');
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

?>
