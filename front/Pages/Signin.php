<?php
session_start(); // Start or resume a session

// Database connection parameters
$servername = "localhost";
$usernam = "root";
$pasword = "";
$dbname = "suivi_pfe";

// Create connection
$conn = new mysqli($servername, $usernam, $pasword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data (sanitize inputs to prevent SQL injection)
$username = $conn->real_escape_string($_POST['username']);
$password = $conn->real_escape_string($_POST['password']);

// SQL query using prepared statement to prevent SQL injection
$sql = "SELECT * FROM etudiant WHERE n_inscription_etudiant=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Compare passwords
    if ($password === $row['password']) {
        // Authentication successful
        session_start();
        $_SESSION['username'] = $username; // Store username in session
        // Redirect to etudiant.html
        header("Location: etudiant.html");
        exit(); // Ensure no more output is sent
    } else {
        // Invalid password
        echo "Invalid username or password";
    }
} else {
    // User not found
    echo "Invalid username or password";
}
// Close prepared statement and database connection
$stmt->close();
$conn->close();
