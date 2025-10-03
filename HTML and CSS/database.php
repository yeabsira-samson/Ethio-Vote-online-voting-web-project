<?php
$servername = "localhost";
$username   = "root";        
$password   = "ZAde3518.";            
$dbname     = "ethiovote"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
