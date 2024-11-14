<?php
session_start();
require_once("../connection.php");
require_once("../modules/Analytics.php");

// Check authentication and permissions
if (!isset($_SESSION["user"]) || $_SESSION["usertype"] != 'a') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$level = isset($_GET['level']) ? $_GET['level'] : null;

$analytics = new Analytics($database, $_SESSION["user"], $_SESSION["usertype"]);
$logsData = $analytics->getSystemLogs(['level' => $level], $page, 20);

// Ensure we return all necessary pagination data
$response = [
    'logs' => $logsData['logs'],
    'total' => $logsData['total'],
    'pages' => $logsData['pages'],
    'current_page' => $logsData['current_page']
];

header('Content-Type: application/json');
echo json_encode($response); 