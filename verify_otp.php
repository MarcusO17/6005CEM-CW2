<?php
session_start();

// Check if OTP is set and not expired
if (isset($_GET['otp']) && isset($_SESSION['otp']) && isset($_SESSION['expiryTime'])) {
    $inputOtp = $_GET['otp'];
    $sessionOtp = $_SESSION['otp'];
    $expiryTime = $_SESSION['expiryTime'];

    // Verify OTP and expiry time
    if (time() > $expiryTime) {
        unset($_SESSION['otp'], $_SESSION['expiryTime']); // Expired OTP
        echo "<script>alert('OTP has expired. Please log in again.'); window.location.href='login.php';</script>";
    } elseif ($inputOtp == $sessionOtp) {
        unset($_SESSION['otp'], $_SESSION['expiryTime']); // Clear OTP after verification
        echo "<script>alert('OTP verified successfully!'); window.location.href='patient/index.php';</script>";
    } else {
        echo "<script>alert('Invalid OTP. Please try again.'); window.location.href='login.php';</script>";
    }
} else {
    echo "<script>alert('OTP not found. Please log in again.'); window.location.href='login.php';</script>";
}

?>
