<?php
require 'connect.php';
session_start();

// Assuming $_SESSION['etu_id'] contains the ID of the logged-in student
$student_id = $_SESSION['etu_id'];

// Adjust the SQL query to fetch data based on the logged-in student's ID
$sql = "SELECT 
            e.nom_etudiant, 
            e.prenom_etudiant, 
            e.niveau_id, 
            n.nom_niveau,
            'Etudiant' AS job,
            e.speciality_id
        FROM 
            etudiant e
        JOIN 
            niveau n ON e.niveau_id = n.niveau_id 
        WHERE 
            e.etudiant_id = $student_id";

// Execute the query
$result = $conn->query($sql);

// Check for errors
if (!$result) {
    // Query execution failed, print error and exit
    echo "Error executing query: " . mysqli_error($conn);
    exit;
}

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Output data of the current student
    $row = $result->fetch_assoc();
    $student_specialty_id = $row['speciality_id'];

    echo "<div class='toolbar'>";
    echo "<span>" . htmlspecialchars($row["nom_etudiant"]) . " " . htmlspecialchars($row["prenom_etudiant"]) . "</span>";
    echo "<span>" . htmlspecialchars($row["job"]) . " " . htmlspecialchars($row["nom_niveau"]) . "</span>"; // Displaying the job designation
    echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>";
    echo "</div>";
} else {
    echo "0 results"; // This line indicates that no matching student was found, adjust if necessary
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Choisir Un Sujet</title>
    <style>
        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            height: 8%;
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
        <h1>La Liste Des Themes en attente :</h1>
    </div>
    <?php
    require 'connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme_id']) && isset($_POST['student_id']) && isset($_POST['binome_id'])) {
        $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
        $binome_id = mysqli_real_escape_string($conn, $_POST['binome_id']);
        $theme_id = mysqli_real_escape_string($conn, $_POST['theme_id']);

        // Récupérer l'etudiant_id de l'étudiant principal
        $student_query = "SELECT etudiant_id FROM etudiant WHERE n_inscription_etudiant = '$student_id'";
        $student_result = mysqli_query($conn, $student_query);

        // Récupérer l'etudiant_id du binome
        $binome_query = "SELECT etudiant_id FROM etudiant WHERE n_inscription_etudiant = '$binome_id'";
        $binome_result = mysqli_query($conn, $binome_query);

        if ($student_result && mysqli_num_rows($student_result) > 0 && $binome_result && mysqli_num_rows($binome_result) > 0) {
            $student_row = mysqli_fetch_assoc($student_result);
            $binome_row = mysqli_fetch_assoc($binome_result);

            $etudiant1_id = $student_row['etudiant_id'];
            $etudiant2_id = $binome_row['etudiant_id'];

            // Vérifier si l'étudiant principal ou le binôme a déjà soumis une demande d'encadrement
            $check_query = "SELECT * FROM binome WHERE etudiant1_id = '$etudiant1_id' OR etudiant2_id = '$etudiant1_id' OR etudiant1_id = '$etudiant2_id' OR etudiant2_id = '$etudiant2_id'";
            $check_result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($check_result) == 0) {
                // Récupérer l'enseignant_id et niveau_id à partir du theme
                $encadrant_query = "SELECT enseignant_id, niveau_id FROM theme WHERE theme_id = '$theme_id'";
                $encadrant_result = mysqli_query($conn, $encadrant_query);

                if ($encadrant_result && mysqli_num_rows($encadrant_result) > 0) {
                    $row = mysqli_fetch_assoc($encadrant_result);
                    $encadrant_id = $row['enseignant_id'];
                    $niveau_id = $row['niveau_id'];

                    // Insérer la demande d'encadrement dans la table binome
                    $insert_query = "INSERT INTO binome (enseignant_id, etudiant1_id, etudiant2_id, niveau_id, theme_id, status, date_created) 
                                     VALUES ('$encadrant_id', '$etudiant1_id', '$etudiant2_id', '$niveau_id', '$theme_id', 'en_attente', NOW())";

                    if (mysqli_query($conn, $insert_query)) {
                        echo "Demande d'encadrement soumise avec succès.";
                    } else {
                        echo "Erreur lors de la soumission de la demande: " . mysqli_error($conn);
                    }
                } else {
                    echo "Erreur: Aucun encadrant trouvé pour le thème sélectionné.";
                }
            } else {
                echo "Erreur: Vous avez déjà soumis une demande d'encadrement.";
            }
        } else {
            echo "Erreur: Numéro d'inscription étudiant invalide.";
        }
    }

    $query = "SELECT t.*, e.nom_enseignant 
              FROM theme t
              JOIN enseignant e ON t.enseignant_id = e.enseignant_id
              WHERE t.status = 'en_attente' AND t.speciality_id = $student_specialty_id";

    $rows = mysqli_query($conn, $query);
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table class="table">
                <tr>
                    <th>Id</th>
                    <th>Titre</th>
                    <th>Stage</th>
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
                        <td><?php echo htmlspecialchars($row['stage']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                        <td><button onclick="showModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Details</button></td>
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
                    <label for="student_id">Entrer Votre numero d'inscription:</label>
                    <input type="number" name="student_id" id="studentId" required><br>
                    <label for="binome_id">Entrer le numero d'inscription de votre binome:</label>
                    <input type="number" name="binome_id" id="binomeId"><br>
                    <button type="submit">Demande l'encadrement</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(theme) {
            document.getElementById('themeDetails').innerText =
                "Titre: " + theme.title_theme + "\n" +
                "Description: " + theme.description_theme + "\n" +
                "Objectives: " + theme.objectif_theme + "\n" +
                "Les Outils: " + theme.outils_theme + "\n" +
                "Les Connaissances: " + theme.connaissances_theme;
            document.getElementById('themeId').value = theme.theme_id;
            document.getElementById('ThemModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('ThemModal').style.display = 'none';
        }
    </script>

</body>

</html>