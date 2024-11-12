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
    if ($_POST['user_id']=='p'){
        $_SESSION['usertype']='p';
            
        header('location: patient/index.php');
    }
 
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
