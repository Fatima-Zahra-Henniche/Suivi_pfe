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
    echo "<span class'logout' ><a href='logout.php'>Déconnexion</a></span>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Liste des Themes Attribue L3</title>
    <style>
        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            /* Updated to accommodate the logout button */
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Added box shadow for better visibility */
        }

        .toolbar .logout {
            justify-self: end;
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
            padding-top: 50px;
        }

        /* Set the width and height of the image */
        .image-container {
            text-align: center;
            /* Center the image horizontally */
        }

        .image-container img {
            width: 40%;
            /* Adjust as needed */
            height: 55%;
            margin: 0 auto;
            margin-top: 40px;
            /* Center the image horizontally */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Liste des Themes Attribue</h1>
    </div>
    <?php
    require 'connect.php';
    $etudiant_id = $_SESSION['etu_id'];

    // Obtenir la spécialité du etudiant
    $etudiant_query = "SELECT speciality_id FROM etudiant WHERE etudiant_id = $etudiant_id";
    $etudiant_result = mysqli_query($conn, $etudiant_query);
    $etudiant_row = mysqli_fetch_assoc($etudiant_result);
    $etudiant_speciality_id = $etudiant_row['speciality_id'];

    // Correct the SQL query to properly join the tables and select the necessary columns
    $query = "SELECT t.theme_id, t.stage, t.title_theme, e.nom_enseignant, 
                    et1.nom_etudiant AS etudiant1_nom, et1.prenom_etudiant AS etudiant1_prenom,
                    et2.nom_etudiant AS etudiant2_nom, et2.prenom_etudiant AS etudiant2_prenom
                FROM theme t
                JOIN enseignant e ON t.enseignant_id = e.enseignant_id
                JOIN binome b ON t.theme_id = b.theme_id
                JOIN etudiant et1 ON b.etudiant1_id = et1.etudiant_id
                JOIN etudiant et2 ON b.etudiant2_id = et2.etudiant_id
                WHERE t.status = 'attribue' AND t.speciality_id = $etudiant_speciality_id";

    $rows = mysqli_query($conn, $query);

    // Check if the query was successful
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table class="table">
                <tr>
                    <th>Id</th>
                    <th>Le Titre</th>
                    <th>Stage</th>
                    <th>Encadrant</th>
                    <th>Binome</th>
                </tr>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($rows)) :
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['title_theme']); ?></td>
                        <td><?php echo htmlspecialchars($row['stage']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['etudiant1_nom']) . ' ' . htmlspecialchars($row['etudiant1_prenom']); ?><br>
                            <?php echo htmlspecialchars($row['etudiant2_nom']) . ' ' . htmlspecialchars($row['etudiant2_prenom']); ?><br>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
    <?php
        } else {
            echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
        }
    } else {
        echo "Erreur dans la requête: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</body>

</html>