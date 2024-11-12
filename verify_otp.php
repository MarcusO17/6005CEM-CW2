<?php

session_start();

$otp = implode('', $_POST['otp']);

if ($_SESSION['otp'] == NULL) {
    die("OTP not generated.");
}

$generatedOtp = $_SESSION['otp'];
$expiryTime = $_SESSION['expiryTime'];

$currentTime = time();
if ($otp == $generatedOtp && $currentTime <= $expiryTime) {
    echo "OTP verified successfully!";
   
    unset($_SESSION['otp']);
    unset($_SESSION['expiryTime']);
 
} else {
    if ($currentTime > $expiryTime) {
        echo "OTP expired. Please request a new one.";
        
        unset($_SESSION['otp']);
        unset($_SESSION['expiryTime']);
        
    } else {
        echo "Invalid OTP. Please try again.";
    }
}

?>
