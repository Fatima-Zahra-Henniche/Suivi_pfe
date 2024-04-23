<?php
// send_emails.php

// Establish database connection (Replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Suivi_pfe";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve email addresses of all enseignants from the database
$sql = "SELECT email_enseignant FROM Enseignant";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each row and send email to enseignants
    while ($row = $result->fetch_assoc()) {
        $to_email = $row["email_enseignant"];
        $subject = "Proposing Themes";
        $message = "You can start proposing themes.";
        $headers = "From: your_email@example.com";

        // Send email
        if (mail($to_email, $subject, $message, $headers)) {
            echo "Email sent successfully to: " . $to_email . "<br>";
        } else {
            echo "Email sending failed to: " . $to_email . "<br>";
        }
    }
} else {
    echo "No enseignant found in the database.";
}
$conn->close();
