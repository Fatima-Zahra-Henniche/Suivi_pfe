<?php
require 'connect.php';
session_start();

if (isset($_SESSION['ens_id'])) {
    $ens_id = $_SESSION['ens_id'];
    $type = 'enseignant';

    // First Query Block: Fetching the enseignant information
    $sql = "SELECT nom_enseignant, prenom_enseignant, 'Enseignant' AS job FROM enseignant WHERE type = ? AND enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $ens_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . htmlspecialchars($row["nom_enseignant"]) . " " . htmlspecialchars($row["prenom_enseignant"]) . "</span>";
                echo "<span>" . htmlspecialchars($row["job"]) . "</span>";
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

    // Second Query Block: Fetching the list of demands
    $query = "SELECT 
                    b.binome_id AS id,
                    t.theme_id AS theme_id,
                    t.title_theme AS title_theme,
                    e1.nom_etudiant AS etudiant1_nom,
                    e1.prenom_etudiant AS etudiant1_prenom,
                    e2.nom_etudiant AS etudiant2_nom,
                    e2.prenom_etudiant AS etudiant2_prenom,
                    n.nom_niveau AS nom_niveau
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
                WHERE 
                    b.enseignant_id = ? AND b.status = 'en_attente'";

    $stmt = $conn->prepare($query);

    $tableContent = ''; // Initialize a variable to store the table content

    if ($stmt) {
        $stmt->bind_param("i", $ens_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tableContent .= '<table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Titre</th>
                            <th>Binome</th>
                            <th>Niveau</th>
                            <th>Decision</th>
                        </tr>
                    </thead>';
            $i = 1;
            while ($row = $result->fetch_assoc()) {
                $tableContent .= '<tbody>
                        <tr>
                            <td>' . $i++ . '</td>
                            <td>' . htmlspecialchars($row['title_theme']) . '</td>
                            <td>' . htmlspecialchars($row['etudiant1_nom']) . ' ' . htmlspecialchars($row['etudiant1_prenom']) . '<br>' . htmlspecialchars($row['etudiant2_nom']) . ' ' . htmlspecialchars($row['etudiant2_prenom']) . '</td>
                            <td>' . htmlspecialchars($row['nom_niveau']) . '</td>
                            <td>
                                <button class="accept-btn" data-binome-id="' . $row['id'] . '" data-theme-id="' . $row['theme_id'] . '">Accepter</button>
                                <button class="reject-btn" data-binome-id="' . $row['id'] . '">Refuser</button>
                            </td>
                        </tr>
                    </tbody>';
            }
            $tableContent .= '</table>';
        } else {
            $tableContent = "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
        }
        $stmt->close();
    } else {
        $tableContent = "Failed to prepare the SQL statement: " . $conn->error;
    }

    $conn->close();
} else {
    $tableContent = "Enseignant ID not set in session.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Liste Des demandes</title>
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
    </style>

</head>

<body>
    <div class="container">
        <h1>les demandes de votre encadrement:</h1>
        <?php echo $tableContent; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const acceptButtons = document.querySelectorAll('.accept-btn');
            const rejectButtons = document.querySelectorAll('.reject-btn');

            acceptButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const binomeId = this.getAttribute('data-binome-id');
                    const themeId = this.getAttribute('data-theme-id');
                    updateStatus(binomeId, themeId, 'attribue', 'attribue', true);
                });
            });

            rejectButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const binomeId = this.getAttribute('data-binome-id');
                    updateStatus(binomeId, null, 'refuse', null, false);
                });
            });
        });

        function updateStatus(binomeId, themeId, binomeStatus, themeStatus, deleteOthers) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_status.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('Status updated successfully');
                    location.reload();
                }
            };

            xhr.send('binome_id=' + binomeId + '&theme_id=' + themeId + '&binome_status=' + binomeStatus + '&theme_status=' + themeStatus + '&delete_others=' + deleteOthers);
        }
    </script>
</body>

</html>