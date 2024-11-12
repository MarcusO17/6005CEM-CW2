<?php

session_start();

include('../session_handler.php');

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

if ($_POST) {
    if (isset($_POST["medication"])) {
        $appointment_id = $_POST['appointment_id'];
        $userid = $_POST['pid'];
        $medication = $_POST["medication"];
        $dosage = $_POST["dosage"];
        $frequency = $_POST["frequency"];
        $additional_notes = $_POST["notes"];

        $sql = "INSERT INTO prescription (pid, appointment_id, medication, dosage, frequency, additional_notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iissss", $userid, $appointment_id, $medication, $dosage, $frequency, $additional_notes);
        if ($stmt->execute()) {

            header("location: appointment.php?action=prescription-added&id=".$appointment_id."&titleget=none");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
