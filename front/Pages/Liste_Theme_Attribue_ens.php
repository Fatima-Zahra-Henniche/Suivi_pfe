<?php
require 'connect.php';
session_start();

if (isset($_SESSION['ens_id'])) {
    $ens_id = $_SESSION['ens_id'];
    $type = 'enseignant';

    // Fetch teacher's name and surname
    $sql = "SELECT nom_enseignant, prenom_enseignant FROM Enseignant WHERE type = ? AND enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $ens_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . htmlspecialchars($row["nom_enseignant"]) . " " . htmlspecialchars($row["prenom_enseignant"]) . "</span>";
                echo "<span>Enseignant</span>";
                echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>";
                echo "</div>";
            }
        } else {
            echo "No results found.";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement: " . htmlspecialchars($conn->error);
    }

    // Fetch the departement_id for the logged-in teacher
    $sql = "SELECT departement_id FROM Enseignant WHERE enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Failed to prepare the SQL statement (departement_id): " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $ens_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $departement_id = $row['departement_id'];

        // Fetch the filiere_id associated with the departement_id
        $query = "SELECT filiere_id FROM Filieres WHERE departement_id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            die("Failed to prepare the SQL statement (filiere_id): " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("i", $departement_id);
        $stmt->execute();
        $filiere_id_result = $stmt->get_result();

        if ($filiere_id_result->num_rows > 0) {
            $filiere_row = $filiere_id_result->fetch_assoc();
            $filiere_id = $filiere_row['filiere_id'];

            // Fetch Speciality options for the same filiere
            $query = "SELECT speciality_id, nom_speciality FROM Speciality WHERE filiere_id = ?";
            $stmt = $conn->prepare($query);

            if (!$stmt) {
                die("Failed to prepare the SQL statement (speciality): " . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("i", $filiere_id);
            $stmt->execute();
            $specialites_result = $stmt->get_result();

            if ($specialites_result->num_rows > 0) {
                $specialites = $specialites_result->fetch_all(MYSQLI_ASSOC);

                // Fetch Themes grouped by Speciality
                $themes_by_speciality = [];
                foreach ($specialites as $specialite) {
                    $speciality_id = $specialite['speciality_id'];
                    $query = "SELECT t.theme_id, t.stage, t.title_theme, e.nom_enseignant,
                                    et1.nom_etudiant AS etudiant1_nom, et1.prenom_etudiant AS etudiant1_prenom,
                                    et2.nom_etudiant AS etudiant2_nom, et2.prenom_etudiant AS etudiant2_prenom
                                FROM theme t
                                JOIN enseignant e ON t.enseignant_id = e.enseignant_id
                                JOIN binome b ON t.theme_id = b.theme_id
                                JOIN etudiant et1 ON b.etudiant1_id = et1.etudiant_id
                                JOIN etudiant et2 ON b.etudiant2_id = et2.etudiant_id
                                WHERE t.status = 'attribue' AND t.speciality_id = ?";
                    $stmt = $conn->prepare($query);

                    if (!$stmt) {
                        die("Failed to prepare the SQL statement (themes): " . htmlspecialchars($conn->error));
                    }

                    $stmt->bind_param("i", $speciality_id);
                    $stmt->execute();
                    $themes_result = $stmt->get_result();

                    if ($themes_result->num_rows > 0) {
                        $themes = $themes_result->fetch_all(MYSQLI_ASSOC);
                        $themes_by_speciality[htmlspecialchars($specialite['nom_speciality'])] = $themes;
                    }
                }
            } else {
                echo "No specialities found for this filiere.";
            }
        } else {
            echo "No filiere found for this departement.";
        }
    } else {
        echo "No departement found for this enseignant ID.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des themes attribue</title>
    <style>
        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            height: 6%;
            top: 0;
            left: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .toolbar .logout {
            justify-self: end;
            padding-right: 15px;
        }

        .toolbar a {
            color: #333;
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

        .speciality-group {
            margin-bottom: 20px;
        }

        .speciality-group h2 {
            margin-bottom: 10px;
        }

        .theme-table {
            width: 100%;
            border-collapse: collapse;
        }

        .theme-table th,
        .theme-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .theme-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Liste des themes attribue</h1>
    </div>
    <?php if (!empty($themes_by_speciality)) : ?>
        <?php foreach ($themes_by_speciality as $speciality => $themes) : ?>
            <div class="speciality-group">
                <h2><?php echo htmlspecialchars($speciality); ?></h2>
                <table class="theme-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Le Titre</th>
                            <th>Stage</th>
                            <th>Encadrant</th>
                            <th>Binome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($themes as $index => $theme) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($theme['title_theme']); ?></td>
                                <td><?php echo htmlspecialchars($theme['stage']); ?></td>
                                <td><?php echo htmlspecialchars($theme['nom_enseignant']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($theme['etudiant1_nom']) . " " . htmlspecialchars($theme['etudiant1_prenom']); ?>
                                    <br>
                                    <?php echo htmlspecialchars($theme['etudiant2_nom']) . " " . htmlspecialchars($theme['etudiant2_prenom']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="image-container">
            <img src="../images/no_result.png" alt="No results image">
        </div>
    <?php endif; ?>

</body>

</html>