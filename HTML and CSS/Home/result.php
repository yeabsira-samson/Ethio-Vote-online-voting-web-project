<?php
$servername = "localhost";
$username = "root"; 
$password = "ZAde3518."; 
$dbname = "ethiovote"; 

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("CALL GetAllPartyVotes()");
    $stmt->execute();

    $output = '<table border="1" style="width:100%; border-collapse: collapse;">';
    $output .= '<tr><th>Party Name</th><th>Total Votes</th></tr>';

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $output .= '<tr>';
        $output .= '<td>' . htmlspecialchars($row['Party_name']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['total_vote_number']) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';

    echo $output;

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null;
?>