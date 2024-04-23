
<?php
// Connect to MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "suivi_pfe";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>