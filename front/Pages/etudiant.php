<?php
require 'connect.php';

session_start();

// Assuming $_SESSION['student_id'] contains the ID of the logged-in student
$student_id = $_SESSION['etu_id'];

// Adjust the SQL query to fetch data based on the logged-in student's ID
$sql = "SELECT 
            e.nom_etudiant, 
            e.prenom_etudiant, 
            e.niveau_id, 
            n.nom_niveau,
            'etudiant' AS job 
            FROM 
                etudiant e
            JOIN 
                niveau n ON e.niveau_id = n.niveau_id 
            WHERE 
                e.etudiant_id = $student_id
            ";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of the current student
    $row = $result->fetch_assoc();

    echo "<div class='toolbar'>";
    echo "<span>" . $row["nom_etudiant"] . " " . $row["prenom_etudiant"] . "</span>";
    echo "<span>" . $row["job"] . $row["nom_niveau"] . "</span>"; // Displaying the job designation
    echo "<span><a href='logout.php'>Déconnexion</a></span>";
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

<body>
    <div class="container">
        <div class="header">
            <h1>Page d'etudiant</h1>
        </div>
        <div class="content">
            <div class="sidebar">
                <ul>
                    <li><a href="Choisir.php">Liste des Themes</a></li>
                    <li><a href="Liste_Theme_L3.php">Liste des Encadrants</a></li>
                    <li><a href="etudiant.php">Planning</a></li>
                </ul>
            </div>
        </div>
    </div>

</html>