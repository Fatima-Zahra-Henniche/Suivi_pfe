<?php
require 'connect.php';

session_start();

if (isset($_SESSION['ens_id'])) {
    $ens_id = $_SESSION['ens_id'];
    $type = 'enseignant';

    $sql = "SELECT nom_enseignant, prenom_enseignant, 'Enseignant' AS job FROM enseignant WHERE type = ? AND enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $ens_id); // Bind type as string (s) and ens_id as integer (i)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>" . $row["job"] . "</span>";
                echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>";
                echo "</div>";
            }
        } else {
            echo "0 results";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Enseignant ID not set in session.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Les encadremant</title>
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
            height: 8%;
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
            margin-top: 60px;
        }

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
        <h1>Liste Des Encadrement :</h1>
    </div>
    <?php
    require 'connect.php';
    $ens_id = $_SESSION['ens_id'];

    $query = "SELECT 
                b.binome_id AS id,
                t.theme_id AS theme_id,
                t.title_theme AS title_theme,
                e1.nom_etudiant AS etudiant1_nom,
                e1.prenom_etudiant AS etudiant1_prenom,
                e2.nom_etudiant AS etudiant2_nom,
                e2.prenom_etudiant AS etudiant2_prenom,
                n.nom_niveau AS nom_niveau,
                s.nom_speciality AS nom_speciality
            FROM 
                binome b
            JOIN 
                Theme t ON b.theme_id = t.theme_id
            JOIN 
                Etudiant e1 ON b.etudiant1_id = e1.etudiant_id
            JOIN 
                Etudiant e2 ON b.etudiant2_id = e2.etudiant_id
            JOIN 
                Niveau n ON b.niveau_id = n.niveau_id
            JOIN
                Speciality s ON t.speciality_id = s.speciality_id
            WHERE 
                b.enseignant_id = ? 
                AND b.status = 'attribue'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ens_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<table class='table table-striped'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th scope='col'>N°</th>";
        echo
        "<th scope='col'>Titre</th>";
        echo "<th scope='col'>Etudiants</th>";
        echo "<th scope='col'>Niveau</th>";
        echo "<th scope='col'>Speciality</th>";
        echo "<th scope='col'>Taux Memoire</th>";
        echo "<th scope='col'>Taux Logiciel</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["title_theme"] . "</td>";
            echo "<td>
                    " . $row["etudiant1_nom"] . " " . $row["etudiant1_prenom"] . "</br>
                    " . $row["etudiant2_nom"] . " " . $row["etudiant2_prenom"] . "
                </td>";
            echo "<td>" . $row["nom_niveau"] . "</td>";
            echo "<td>" . $row["nom_speciality"] . "</td>";
            echo "<form action='import_taux.php' method='post'>";
            echo "<td><input type='number' name='taux_memoire' min='0' max='100' required></td>";
            echo "<td><input type='number' name='taux_logiciel' min='0' max='100' required></td>";
            echo "<td><input type='hidden' name='binome_id' value='" . $row["id"] . "'></td>";
            echo "<td><input type='submit' value='Valider'></td>";
            echo "</form>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
    }
    $stmt->close();
    $conn->close();
    ?>

</body>

</html>