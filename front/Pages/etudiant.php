<?php
require 'connect.php';

session_start();

// Assuming $_SESSION['student_id'] contains the ID of the logged-in student
$student_id = $_SESSION['etu_id'];

// Adjust the SQL query to fetch data based on the logged-in student's ID
$sql = "SELECT 
            e.nom_etudiant, 
            e.prenom_etudiant, 
            e.speciality_id, 
            s.nom_speciality,
            'Etudiant' AS job 
            FROM 
                etudiant e
            JOIN 
                speciality s ON e.speciality_id = s.speciality_id 
            WHERE 
                e.etudiant_id = $student_id
            ";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of the current student
    $row = $result->fetch_assoc();

    echo "<div class='toolbar'>";
    echo "<span>" . $row["nom_etudiant"] . " " . $row["prenom_etudiant"] . "</span>";
    echo "<span>" . $row["job"] . " " . $row["nom_speciality"] . "</span>"; // Displaying the job designation
    echo "<span class='logout' ><a href='logout.php'>Déconnexion</a></span>";
    echo "</div>";
} else {
    echo "0 results";
}

$_SESSION['etu_speciality_id'] = $row["speciality_id"];

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F4F4F4;
        }

        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            /* Updated to accommodate the logout button */
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            height: 5%;
            top: 0;
            left: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Added box shadow for better visibility */
        }

        .toolbar .logout {
            justify-self: end;
            padding-right: 15px;
            /* Aligns the logout button to the end of the grid */
        }

        .toolbar a {
            color: #333;
            /* Adjusted link color */
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #333;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .toolbar a:hover {
            background-color: #333;
            color: #BED1FC;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #F1F8FF;
        }

        .content {
            background-color: #E4E4E4;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: block;
            padding: 10px 15px;
            background-color: #7D80C7;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #acaff1;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <div class="sidebar">
                <ul>
                    <li><a href="Choisir.php">Choisir Un Theme</a></li>
                    <li><a href="Liste_Theme_etu.php">Liste des Themes Attribue</a></li>
                    <li><a href="planning_etu.php">Planning</a></li>
                </ul>
            </div>
        </div>
    </div>

</html>