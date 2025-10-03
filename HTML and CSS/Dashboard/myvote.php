<?php
session_start();
include '../database.php'; 

header("Content-Type: application/javascript"); 

if (!isset($_SESSION['voter_id'])) {
    echo "alert('You must login first.'); window.location.href='login.html';";
    exit();
}

$voterIdent = $_SESSION['voter_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $candidateId = $_POST['id'];

    $stmt = $conn->prepare("CALL My_Vote(?, ?)");
    $stmt->bind_param("is", $candidateId, $voterIdent);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $message = $row['Message'];
        } else {
            $message = "Unexpected server response. Please try again.";
        }

        while ($conn->more_results() && $conn->next_result()) {}
        echo "alert('" . addslashes($message) . "');";
    } else {
        echo "alert('Error executing vote: " . addslashes($stmt->error) . "');";
    }

    $stmt->close();
} else {
    echo "alert('Invalid candidate.');";
}

$conn->close();
?>
