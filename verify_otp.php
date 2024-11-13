<?php

include("connection.php");

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
        session_regenerate_id(true);

        //   Patient dashbord
        $_SESSION['usertype']='p';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      
        include('session_handler.php');
        resetAccountLock($database,$_SESSION['user']);
        header('location: patient/index.php');
    }
    if ($_POST['user_id']=='d'){
        session_regenerate_id(true);

        //   doctor dashbord
        $_SESSION['usertype']='d';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        include('session_handler.php');
        resetAccountLock($database,$_SESSION['user']);
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

function resetAccountLock($database,$email){
    $sql1=  " UPDATE webuser SET attempts=NULL, last_recorded_attempt=NULL, end_lockout=NULL where email='$email';";
    $database->query($sql1);
}

?>
