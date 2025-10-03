<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$status = isset($_SESSION['voter_id']) ? 'loggedin' : 'loggedout';

header('Content-Type: application/json');
echo json_encode([
    'status' => $status,
    'message' => $status === 'loggedin' ? 'Welcome back!' : 'Please log in.'
]);
?>
