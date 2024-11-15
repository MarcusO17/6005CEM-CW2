<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
  header("HTTP/1.1 403 Forbidden");
  exit;
}

include("../connection.php");

$email = $_GET['email'] ?? '';
$usertype = $_GET['usertype'] ?? '';

$response = [];

if ($usertype === 'p') {
  // Fixed patient query: Including all necessary fields in GROUP BY
  $stmt = $database->prepare("
        SELECT 
            p.pid,
            p.pemail,
            p.pname,
            p.ptel,
            COUNT(DISTINCT a.appoid) as appointment_count
        FROM patient p
        LEFT JOIN appointment a ON p.pid = a.pid
        WHERE p.pemail = ?
        GROUP BY p.pid, p.pemail, p.pname, p.ptel
    ");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $response = $result->fetch_assoc();
} else if ($usertype === 'd') {
  // Fixed doctor query: Including all necessary fields in GROUP BY
  $stmt = $database->prepare("
        SELECT 
            d.docid,
            d.docemail,
            d.docname,
            d.doctel,
            s.sname as specialty,
            (SELECT COUNT(DISTINCT a.pid) 
             FROM appointment a 
             INNER JOIN schedule sch2 ON a.scheduleid = sch2.scheduleid 
             WHERE sch2.docid = d.docid) as patient_count,
            COUNT(DISTINCT sch.scheduleid) as session_count
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        LEFT JOIN schedule sch ON d.docid = sch.docid
        WHERE d.docemail = ?
        GROUP BY d.docid, d.docemail, d.docname, d.doctel, s.sname
    ");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $response = $result->fetch_assoc();
}

header('Content-Type: application/json');
echo json_encode($response);
