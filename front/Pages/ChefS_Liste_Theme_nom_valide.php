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
    <title>Liste des Themes non Valide</title>
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
            margin-top: 50px;
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

        /* Modal styles */
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
        <h1>Liste des Themes non Valide</h1>
    </div>
    <?php
    require 'connect.php';

    $chef_id = $_SESSION['chef_id'];

    // Obtenir la spécialité du chef
    $chef_query = "SELECT speciality_id FROM enseignant WHERE enseignant_id = $chef_id";
    $chef_result = mysqli_query($conn, $chef_query);
    $chef_row = mysqli_fetch_assoc($chef_result);
    $chef_speciality_id = $chef_row['speciality_id'];

    // Requête SQL pour obtenir les thèmes non validés avec le nom de l'enseignant
    $query = "SELECT t.*, e.nom_enseignant 
              FROM theme t
              JOIN enseignant e ON t.enseignant_id = e.enseignant_id
              WHERE t.status = 'non_valide' AND t.speciality_id = $chef_speciality_id";
    $rows = mysqli_query($conn, $query);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $theme_id = intval($_POST['theme_id']);
        $action = $_POST['action'];

        if ($action === 'accepte') {
            $query = "UPDATE theme SET status = 'en_attente' WHERE theme_id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $theme_id);
                if ($stmt->execute()) {
                    $message = "Theme accepté avec succès.";
                } else {
                    $message = "Erreur lors de l'acceptation du thème: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Erreur lors de la préparation de la requête (accepte): " . $conn->error;
            }

            echo "<script type='text/javascript'>
                alert('$message');
                window.location.href = 'ChefS_Liste_Theme_nom_valide.php';
            </script>";
        } elseif ($action === 'refuse') {
            $query = "DELETE FROM theme WHERE theme_id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $theme_id);
                $stmt->execute();
                $stmt->close();
                header("Location: ChefS_Liste_Theme_nom_valide.php");
            } else {
                echo "Erreur lors de la préparation de la requête (refuse): " . $conn->error;
            }
        }
    }

    // Vérifiez si la requête a réussi
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Le Titre</th>
                        <th>Stage</th>
                        <th>Encadrant</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <?php
                $i = 1;
                foreach ($rows as $row) :
                ?>
                    <tbody>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['title_theme']); ?></td>
                            <td><?php echo htmlspecialchars($row['stage']); ?></td>
                            <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                            <td>
                                <button onclick="showModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="btn btn-light mb-2">Details</button>
                            </td>
                        </tr>
                    </tbody>
                <?php endforeach; ?>
            </table>
    <?php
        } else {
            echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
        }
    } else {
        echo "Erreur dans la requête: " . mysqli_error($conn);
    }
    ?>

    <div id="ThemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('ThemModal').style.display='none'">X</span>
            <div class="container">
                <h2>Details du Sujet</h2>
                <p id="themeDetails"></p>
                <form id="themeForm" method="POST">
                    <input type="hidden" name="theme_id" id="themeId">
                    <input type="hidden" name="action" id="themeAction">
                    <button type="button" onclick="submitForm('accepte')" class="btn btn-light mb-2">Accepte</button>
                    <button type="button" onclick="confirmRefuse()" class="btn btn-light mb-2">Refuse</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(theme) {
            document.getElementById('themeDetails').innerText =
                "Titre: " + theme.title_theme + "\n" +
                "Encadrant: " + theme.nom_enseignant + "\n" +
                "Description: " + theme.description_theme + "\n" +
                "Objectives: " + theme.objectif_theme + "\n" +
                "Les Outils: " + theme.outils_theme + "\n" +
                "Les Connaisances: " + theme.connaissances_theme;
            document.getElementById('themeId').value = theme.theme_id;
            document.getElementById('ThemModal').style.display = 'block';
        }

        function submitForm(action) {
            document.getElementById('themeAction').value = action;
            document.getElementById('themeForm').submit();
        }

        function confirmRefuse() {
            if (confirm("Êtes-vous sûr de vouloir refuser ce thème ?")) {
                submitForm('refuse');
            }
        }
    </script>
</body>

</html>