<?php require 'connect.php';

session_start();

if (isset($_SESSION['chef_id'])) {
    $chef_id = $_SESSION['chef_id'];
    $type = 'chef_specialite';
    $sql = "SELECT e.nom_enseignant, e.prenom_enseignant, e.speciality_id, s.nom_speciality, 'chef speciality' AS job FROM enseignant e join speciality s ON e.speciality_id = s.speciality_id WHERE e.type = ? AND e.enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $chef_id); // Bind type as string (s) and ens_id as integer (i)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>" . $row["job"] . " " . $row["nom_speciality"] . " </span>";
                echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>"; // Modified to French "Déconnexion"
                echo "</div>";
            }
        } else {
            echo "0 results";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement: " . $conn->error;
    }

    // $conn->close();
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
    <title>Planning Table</title>
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
        <h1> Table De Planning</h1>
        <?php
        require 'connect.php';

        try {
            $rows = $conn->query("SELECT 
                                    pl.planning_id,
                                    CONCAT(e1.nom_etudiant, ' ', e1.prenom_etudiant) AS nom_etudiant_01,
                                    CONCAT(e2.nom_etudiant, ' ', e2.prenom_etudiant) AS nom_etudiant_02,
                                    CONCAT(s.nom_enseignant, ' ', s.prenom_enseignant) AS nom_enseignant,
                                    CONCAT(j1.nom_enseignant, ' ', j1.prenom_enseignant) AS nom_jury_01,
                                    CONCAT(j2.nom_enseignant, ' ', j2.prenom_enseignant) AS nom_jury_02,
                                    pl.date_planning,
                                    pl.heure_debut,
                                    pl.salle
                                FROM 
                                    Planning pl
                                JOIN 
                                    binome b ON pl.binome_id = b.binome_id
                                JOIN 
                                    enseignant s ON pl.enseignant_id = s.enseignant_id
                                JOIN 
                                    etudiant e1 ON b.etudiant1_id = e1.etudiant_id
                                JOIN 
                                    etudiant e2 ON b.etudiant2_id = e2.etudiant_id
                                JOIN 
                                    enseignant j1 ON pl.jury_01 = j1.enseignant_id
                                JOIN 
                                    enseignant j2 ON pl.jury_02 = j2.enseignant_id");

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
                $planning_id = intval($_POST['planning_id']);
                $sql = "DELETE FROM planning WHERE planning_id = $planning_id";
                if ($conn->query($sql) === TRUE) {
                    echo "Record deleted successfully";
                } else {
                    throw new Exception("Error deleting record: " . $conn->error);
                }
            }

            if ($rows) {
                if ($rows->num_rows > 0) {
                    echo "<table class='table'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th scope='col'>N°</th>";
                    echo "<th scope='col'>Etudiant</th>";
                    echo "<th scope='col'>Encadrant</th>";
                    echo "<th scope='col'>jury</th>";
                    echo "<th scope='col'>Date</th>";
                    echo "<th scope='col'>Heure</th>";
                    echo "<th scope='col'>Salle</th>";
                    echo "<th scope='col'>Supprimer</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    while ($row = $rows->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['planning_id'] . "</td>";
                        echo "<td>" . $row['nom_etudiant_01'] . " </br>
                                " . $row['nom_etudiant_02'] . "
                            </td>";
                        echo "<td>" . $row['nom_enseignant'] . "</td>";
                        echo "<td>" . $row['nom_jury_01'] . "</br>
                                " . $row['nom_jury_02'] . "
                            </td>";
                        echo "<td>" . $row['date_planning'] . "</td>";
                        echo "<td>" . $row['heure_debut'] . "</td>";
                        echo "<td>" . $row['salle'] . "</td>";
                        echo "<td>";
                        echo "<form method='POST'>";
                        echo "<input type='hidden' name='planning_id' value='" . $row['planning_id'] . "'>";
                        echo "<input type='hidden' name='action' value='delete'>";
                        echo "<button type='submit' class='btn btn-light mb-2'>Supprimer</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
                }
            } else {
                throw new Exception("Error retrieving data from the database: " . $conn->error);
            }
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
        }
        $conn->close();
        ?>
    </div>
</body>

</html>