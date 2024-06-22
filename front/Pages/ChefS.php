<?php
require 'connect.php';

session_start();

if (isset($_SESSION['chef_id'])) {
    $chef_id = $_SESSION['chef_id'];
    $type = 'chef_specialite';

    $sql = "SELECT e.nom_enseignant, e.prenom_enseignant, e.speciality_id, s.nom_speciality, 'Chef Speciality' AS job 
            FROM enseignant e 
            JOIN speciality s ON e.speciality_id = s.speciality_id 
            WHERE e.type = ? AND e.enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $chef_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . htmlspecialchars($row["nom_enseignant"]) . " " . htmlspecialchars($row["prenom_enseignant"]) . "</span>";
                echo "<span>" . htmlspecialchars($row["job"]) . " " . htmlspecialchars($row["nom_speciality"]) . " </span>";
                echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>";
                echo "</div>";
            }
        } else {
            echo "0 results";
        }

        $stmt->close();

        // Fetch the speciality_id for the logged-in teacher
        $sql_speciality = "SELECT speciality_id FROM enseignant WHERE enseignant_id = ?";
        $stmt_speciality = $conn->prepare($sql_speciality);
        $stmt_speciality->bind_param("i", $chef_id);
        $stmt_speciality->execute();
        $result_speciality = $stmt_speciality->get_result();

        if ($result_speciality->num_rows > 0) {
            $row_speciality = $result_speciality->fetch_assoc();
            $speciality_id = $row_speciality['speciality_id'];
            $_SESSION['Chef_speciality_id'] = $speciality_id;

            // Fetch the filiere_id associated with the speciality
            $sql_filiere = "SELECT filiere_id FROM speciality WHERE speciality_id = ?";
            $stmt_filiere = $conn->prepare($sql_filiere);
            $stmt_filiere->bind_param("i", $speciality_id);
            $stmt_filiere->execute();
            $result_filiere = $stmt_filiere->get_result();

            if ($result_filiere->num_rows > 0) {
                $row_filiere = $result_filiere->fetch_assoc();
                $filiere_id = $row_filiere['filiere_id'];

                // Fetch Speciality options for the same filiere
                $sql_speciality = "SELECT speciality_id, nom_speciality FROM speciality WHERE filiere_id = ?";
                $stmt_speciality = $conn->prepare($sql_speciality);
                $stmt_speciality->bind_param("i", $filiere_id);
                $stmt_speciality->execute();
                $result_speciality = $stmt_speciality->get_result();

                if ($result_speciality->num_rows > 0) {
                    $specialites = $result_speciality->fetch_all(MYSQLI_ASSOC);
                } else {
                    echo "No specialities found for this filiere.";
                }
            } else {
                echo "No filiere found for this speciality.";
            }
        } else {
            echo "No speciality found for this enseignant ID.";
        }
    } else {
        echo "Failed to prepare the SQL statement: " . $conn->error;
    }
} else {
    echo "Chef ID not set in session.";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFE Admin</title>
    <link rel="stylesheet" href="../Styles/ChefS.css">
</head>

<body>

    <!-- Menu -->
    <div class="Menu">
        <div>
            <ul>
                <li><button onclick="document.getElementById('emailModal').style.display='block'"> Lancer la proposition </button></li>
                <li><button onclick="document.getElementById('studentModal').style.display='block'">Importer Des Etudiants</button></li>
                <li><button onclick="document.getElementById('teacherModal').style.display='block'">Ajouter Un Enseignants</button></li>
                <li><button onclick="document.getElementById('chefModal').style.display='block'">Ajouter Chef Speciality</button></li>
                <li><button onclick="document.getElementById('planingModal').style.display='block'">Saisir Un Planing</button></li>
            </ul>
        </div>
    </div>
    <!-- Home -->
    <div class="Home">
        <button><a href="ChefS_List_etu.php">Liste des etudiants</a></button>
        <button><a href="ChefS_List_ens.php">Liste des enseignants</a></button>
        <button><a href="etu_pas_choisi.php">Les etudiants sans sujet</a></button>
        <button><a href="ChefS_Liste_Theme_nom_valide.php"> Les Themes non valide </a></button>
        <button><a href="Liste_Theme.php">Liste des Encadrement</a></button>
        <button><a href="Planning_Liste.php">Planning Liste</a></button>
    </div>

    <!-- Import EXCEL FILE -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('studentModal').style.display='none'">X</span>
            <div class="container">
                <h2>Upload an EXCEL file please</h2>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="file" name="excel" required>
                    <button type="submit" name="import">Import</button>
                </form>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['import'])) {
        $fileName = $_FILES["excel"]["name"];
        $fileTmpName = $_FILES["excel"]["tmp_name"];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileExtension = strtolower($fileExtension);

        $newFileName = date("Y.m.d") . " - " . date("h.i.s") . "." . $fileExtension;

        $targetDirectory = "uploads/" . $newFileName;

        if (move_uploaded_file($fileTmpName, $targetDirectory)) {
            error_reporting(0);
            ini_set('display_errors', 0);

            require "exelReader/excel_reader2.php";
            require "exelReader/SpreadsheetReader.php";

            try {
                $reader = new SpreadsheetReader($targetDirectory);

                // Prepare the statement to fetch specialite_id
                $sql_fetch_specialite_id = "SELECT speciality_id FROM speciality WHERE nom_speciality = ?";
                $stmt_fetch_specialite_id = $conn->prepare($sql_fetch_specialite_id);

                foreach ($reader as $key => $row) {
                    $nom = $row[0];
                    $prenom = $row[1];
                    $n_insc = $row[2];
                    $birthday = $row[3];
                    $email = $row[4];
                    $speciality_name = $row[5];

                    // Fetch specialite_id using the speciality name
                    $stmt_fetch_specialite_id->bind_param("s", $speciality_name);
                    $stmt_fetch_specialite_id->execute();
                    $result = $stmt_fetch_specialite_id->get_result();

                    if ($result->num_rows > 0) {
                        $speciality_row = $result->fetch_assoc();
                        $specialite_id = $speciality_row['speciality_id'];

                        // Insert the student data into the database
                        $sql_insert = "INSERT INTO `etudiant`(`nom_etudiant`, `prenom_etudiant`, `n_inscription_etudiant`, `email_etudiant`, `speciality_id`, `birthday_etudiant`) 
                                       VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_insert = $conn->prepare($sql_insert);
                        $stmt_insert->bind_param("ssssss", $nom, $prenom, $n_insc, $email, $specialite_id, $birthday);
                        $stmt_insert->execute();
                    } else {
                        echo "Speciality not found for name: " . htmlspecialchars($speciality_name) . "<br>";
                    }
                }

                echo "
                <script>
                alert('Successfully Imported');
                document.location.href = '';
                </script>
                ";
            } catch (Exception $e) {
                echo "Error reading the Excel file: " . $e->getMessage();
            }
        } else {
            echo "Failed to move uploaded file.";
        }
    }
    ?>


    <!-- Send Email -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('emailModal').style.display='none'">X</span>
            <div class="content">
                <p>Do you really want to start the propositions?</p>
                <p>By clicking on the button below, you agree to send emails to the professors telling them that they can start posting their themes.</p>
                <button id="sendEmailButton">Send Email to Enseignants</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#sendEmailButton").click(function() {
                $.ajax({
                    url: 'Send_email.php', // Ensure this path is correct
                    type: 'POST',
                    success: function(response) {
                        alert(response);
                        document.getElementById('emailModal').style.display = 'none';
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Log any errors to console
                        alert('Error sending email. Please try again.');
                    }
                });
            });
        });
    </script>

    <!-- Import teacher -->
    <div id="teacherModal" class="modl">
        <div class="modl-content">
            <span class="close" onclick="document.getElementById('teacherModal').style.display='none'">X</span>
            <div class="contain">
                <h2>Ajouter un enseignant</h2>
                <form action="import_ens.php" method="post">
                    <label for="name">Nom:</label>
                    <input type="text" id="name" name="name"><br><br>

                    <label for="lastName">Prenom:</label>
                    <input type="text" id="lastName" name="lastName"><br><br>

                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email"><br><br>

                    <label for="N_tel">N_tel:</label>
                    <input type="text" id="N_tel" name="N_tel"><br><br>

                    <input type="submit" value="Add Professor">
                </form>
            </div>
        </div>
    </div>

    <!-- Import Chef Speciality -->
    <div id="chefModal" class="modl">
        <div class="modl-content">
            <span class="close" onclick="document.getElementById('chefModal').style.display='none'">X</span>
            <div class="contain">
                <h2>Ajouter un chef speciality</h2>
                <form action="import_Chef_Speciality.php" method="post">
                    <label for="name">Nom:</label>
                    <input type="text" id="name" name="name"><br><br>

                    <label for="lastName">Prenom:</label>
                    <input type="text" id="lastName" name="lastName"><br><br>

                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email"><br><br>

                    <label for="N_tel">N_tel:</label>
                    <input type="text" id="N_tel" name="N_tel"><br><br>

                    <label for="speciality">Speciality:</label>
                    <select id="speciality" name="speciality">
                        <?php
                        if (isset($specialites)) {
                            foreach ($specialites as $specialite) {
                                echo "<option value='" . htmlspecialchars($specialite['speciality_id'], ENT_QUOTES) . "'>" . htmlspecialchars($specialite['nom_speciality'], ENT_QUOTES) . "</option>";
                            }
                        }
                        ?>
                    </select><br><br>

                    <input type="submit" value="Add Chef Speciality">
                </form>
            </div>
        </div>
    </div>

    <!-- Planing -->
    <div id="planingModal" class="modl">
        <div class="modl-content">
            <span class="close" onclick="document.getElementById('planingModal').style.display='none'">X</span>
            <div class="contain">
                <h2>Saisir un planing</h2>
                <form action="import_planing.php" method="post">
                    <label for="theme">Theme:</label>
                    <select id="theme" name="theme">
                        <?php
                        require 'connect.php'; // S'assurer que la connexion est incluse ici pour les requêtes
                        $query = "SELECT title_theme FROM theme WHERE status = 'attribue' AND permission = 'oui' AND speciality_id = '$speciality_id'";
                        $rows = mysqli_query($conn, $query);
                        if ($rows && mysqli_num_rows($rows) > 0) {
                            foreach ($rows as $row) {
                                echo "<option value='" . htmlspecialchars($row['title_theme'], ENT_QUOTES) . "'>" . htmlspecialchars($row['title_theme'], ENT_QUOTES) . "</option>";
                            }
                        }
                        ?>
                    </select><br><br>

                    <label for="jury1">Jury 1 :</label>
                    <select id="jury1" name="jury1">
                        <?php
                        $query = "SELECT nom_enseignant, prenom_enseignant FROM enseignant";
                        $rows = mysqli_query($conn, $query);
                        if ($rows && mysqli_num_rows($rows) > 0) {
                            foreach ($rows as $row) {
                                echo "<option value='" . htmlspecialchars($row['nom_enseignant'] . " " . $row['prenom_enseignant'], ENT_QUOTES) . "'>" . htmlspecialchars($row['nom_enseignant'] . " " . $row['prenom_enseignant'], ENT_QUOTES) . "</option>";
                            }
                        }
                        ?>
                    </select><br><br>

                    <label for="jury2">Jury 2 :</label>
                    <select id="jury2" name="jury2">
                        <?php
                        $query = "SELECT nom_enseignant, prenom_enseignant FROM enseignant";
                        $rows = mysqli_query($conn, $query);
                        if ($rows && mysqli_num_rows($rows) > 0) {
                            foreach ($rows as $row) {
                                echo "<option value='" . htmlspecialchars($row['nom_enseignant'] . " " . $row['prenom_enseignant'], ENT_QUOTES) . "'>" . htmlspecialchars($row['nom_enseignant'] . " " . $row['prenom_enseignant'], ENT_QUOTES) . "</option>";
                            }
                        }
                        ?>
                    </select><br><br>

                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date"><br><br>

                    <label for="heure">Heure:</label>
                    <input type="time" id="heure" name="heure"><br><br>

                    <label for="salle">Salle:</label>
                    <input type="text" id="salle" name="salle"><br><br>

                    <input type="submit" value="Add Planing">
                </form>
            </div>
        </div>
    </div>

</body>

</html>