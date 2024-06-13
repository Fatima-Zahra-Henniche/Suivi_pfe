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
                    $query = "SELECT theme_id, title_theme, stage, description_theme, objectif_theme, outils_theme, connaissances_theme, Enseignant.nom_enseignant
                              FROM Theme 
                              JOIN Enseignant ON Theme.enseignant_id = Enseignant.enseignant_id 
                              WHERE Theme.speciality_id = ? AND Theme.status = 'en_attente'";
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
    <title>Liste des themes non attribue</title>
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
            height: 60%;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Liste des themes non attribue</h1>
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
                            <th>Details</th>
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
                                    <button onclick="showModal(<?php echo htmlspecialchars(json_encode($theme)); ?>)" class="btn btn-light mb-2">Details</button>
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

    <div id="ThemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('ThemModal').style.display='none'">X</span>
            <div class="container">
                <h2>Details du Sujet</h2>
                <p id="themeDetails"></p>
            </div>
        </div>
    </div>

    <script>
        function showModal(theme) {
            document.getElementById('themeDetails').innerText =
                "Titre: " + theme.title_theme + "\n" +
                "Encadrant: " + theme.nom_enseignant + "\n" +
                "Description: " + theme.description_theme + "\n" +
                "Objectifs: " + theme.objectif_theme + "\n" +
                "Les Outils: " + theme.outils_theme + "\n" +
                "Les Connaissances: " + theme.connaissances_theme;
            document.getElementById('ThemModal').style.display = 'block';
        }
    </script>
</body>

</html>