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

if ($_POST) {
    if (isset($_POST["medication"])) {
        $appointment_id = htmlspecialchars($_POST['appointment_id']);
        $userid = htmlspecialchars($_POST['pid']);
        $medication = htmlspecialchars($_POST["medication"]);
        $dosage = htmlspecialchars($_POST["dosage"]);
        $frequency = htmlspecialchars($_POST["frequency"]);
        $additional_notes = htmlspecialchars($_POST["notes"]);

        $sql = "INSERT INTO prescription (pid, appointment_id, medication, dosage, frequency, additional_notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iissss", $userid, $appointment_id, $medication, $dosage, $frequency, $additional_notes);
        if ($stmt->execute()) {

            header("location: appointment.php?action=prescription-added&id=".urlencode($appointment_id)."&titleget=none");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>
