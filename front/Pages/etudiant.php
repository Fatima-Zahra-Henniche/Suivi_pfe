<?php
require 'connect.php';

session_start();

$sql = "SELECT nom_etudiant, prenom_etudiant, 'etudiant' AS job FROM etudiant"; // Corrected here

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<div class='toolbar'>";
        echo "<span>" . $row["nom_etudiant"] . " " . $row["prenom_etudiant"] . "</span>";
        echo "<span>" . $row["job"] . "</span>";
        echo "<span>Les Niveaux</span>";
        echo "</div>";
    }
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