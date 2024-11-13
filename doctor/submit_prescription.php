<?php

session_start();

if (isset($_SESSION["user"])) {
    if ($_SESSION["user"] == "" || $_SESSION['usertype'] != 'd') {
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
    exit();
}


include("../connection.php");

// import EncryptionUtil
require "../utils/encryption-util.php";
use function Utils\encrypt;


if ($_POST) {
    if (isset($_POST["medication"])) {
        $appointment_id = $_POST['appointment_id'];
        $userid = $_POST['pid'];
        $medication = $_POST["medication"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $additional_notes = $_POST["notes"];

        // Encrypt sensitive data
        $encrypted_medication = encrypt($medication);
        $encrypted_dosage = encrypt($dosage);
        $encrypted_frequency = encrypt($frequency);
        $encrypted_additional_notes = encrypt($additional_notes);

        $sql = "INSERT INTO prescription (pid, appointment_id, medication, dosage, frequency, additional_notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iissss", $userid, $appointment_id, $encrypted_medication, $encrypted_dosage, $encrypted_frequency, $encrypted_additional_notes);

        if ($stmt->execute()) {
            header("location: appointment.php?action=prescription-added&id=".$appointment_id."&titleget=none");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
