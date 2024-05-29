<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Themes non Valide</title>
    <style>
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
    <h1>Liste des Themes non Valide</h1>
    <?php
    require 'connect.php';

    // Corrected SQL query with JOIN to fetch teacher's name
    $query = "SELECT t.*, e.nom_enseignant 
              FROM theme t
              JOIN enseignant e ON t.enseignant_id = e.enseignant_id
              WHERE t.status = 'non_valide' AND t.niveau_id = 1";
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

    // Check if query was successful
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table border="1">
                <tr>
                    <th>Id</th>
                    <th>Le Titre</th>
                    <th>Encadrant</th>
                    <th>Details</th>
                </tr>
                <?php
                $i = 1;
                foreach ($rows as $row) :
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['title_theme']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                        <td>
                            <button onclick="showModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Details</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
    <?php
        } else {
            echo "Aucun résultat trouvé.";
        }
    } else {
        echo "Erreur dans la requête: " . mysqli_error($conn);
    }
    ?>

    <div id="ThemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('ThemModal').style.display='none'">X</span>
            <div class="container">
                <h2>Details du Theme</h2>
                <p id="themeDetails"></p>
                <form id="themeForm" method="POST">
                    <input type="hidden" name="theme_id" id="themeId">
                    <input type="hidden" name="action" id="themeAction">
                    <button type="button" onclick="submitForm('accepte')">Accepte</button>
                    <button type="button" onclick="confirmRefuse()">Refuse</button>
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