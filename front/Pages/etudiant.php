<?php
require 'connect.php';

session_start();

// Assuming $_SESSION['student_id'] contains the ID of the logged-in student
$student_id = $_SESSION['etu_id'];

// Adjust the SQL query to fetch data based on the logged-in student's ID
$sql = "SELECT nom_etudiant, prenom_etudiant, 'etudiant' AS job FROM etudiant WHERE etudiant_id = $student_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of the current student
    $row = $result->fetch_assoc();

    echo "<div class='toolbar'>";
    echo "<span>" . $row["nom_etudiant"] . " " . $row["prenom_etudiant"] . "</span>";
    echo "<span>" . $row["job"] . "</span>"; // Displaying the job designation
    echo "</div>";
} else {
    echo "0 results";
}

$conn->close();

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Styles/EtuPage.css">
    <title>PFE</title>
</head>

<!-- <body>
    <div class="toolbar">
        <span>Nom Prenom</span>
        <span>job</span>
        <span>Niveaux</span>
    </div>
</body> -->

</html>