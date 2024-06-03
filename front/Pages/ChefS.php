<?php
require 'connect.php';

session_start();

if (isset($_SESSION['chef_id'])) {
    $chef_id = $_SESSION['chef_id'];
    $type = 'chef_specialite';

    $sql = "SELECT e.nom_enseignant, e.prenom_enseignant, e.speciality_id, s.nom_speciality, 'chef specialite' AS job FROM enseignant e JOIN speciality s ON e.speciality_id = s.speciality_id WHERE e.type = ? AND e.enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $chef_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>" . $row["job"] . " " . $row["nom_speciality"] . " </span>";
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
            $sql_filiere = "SELECT filiere_id FROM Niveau WHERE niveau_id = (
                                SELECT niveau_id FROM Speciality WHERE speciality_id = ?
                            )";
            $stmt_filiere = $conn->prepare($sql_filiere);
            $stmt_filiere->bind_param("i", $speciality_id);
            $stmt_filiere->execute();
            $result_filiere = $stmt_filiere->get_result();

            if ($result_filiere->num_rows > 0) {
                $row_filiere = $result_filiere->fetch_assoc();
                $filiere_id = $row_filiere['filiere_id'];

                // Fetch Speciality options for the same filiere
                $sql_speciality = "SELECT speciality_id, nom_speciality FROM Speciality WHERE niveau_id IN (
                                        SELECT niveau_id FROM Niveau WHERE filiere_id = ?
                                    )";
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

    $conn->close();
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
                <li><button onclick="document.getElementById('planingModal').style.display='block'">Saisir Un Planing</button></li>
            </ul>
        </div>
    </div>
    <!-- Home -->
    <div class="Home">
        <button><a href="ChefS_List_etu.php">Liste des etudiants</a></button>
        <button><a href="ChefS_List_ens.php">Liste des enseignants</a></button>
        <button><a href="ChefS_Liste_Theme_nom_valide.php"> Themes non valide </a></button>
        <button><a href="Liste_Theme.php">Liste des themes</a></button>
        <button><a href="Planning_Liste.php">Planning Liste</a></button>
    </div>



    <!-- Import EXCEL FILE -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('studentModal').style.display='none'">X</span>
            <div class="container">
                <h2>Upload an EXCEL file please</h2>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="file" name="excel" required value="">
                    <button type="submit" name="import">Import</button>
                </form>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['import'])) {
        $fileName = $_FILES["excel"]["name"];
        $fileExtension = explode('.', $fileName);
        $fileExtension = strtolower(end($fileExtension));

        $newFileName = date("d.m.Y") . " - " . date("h.i.s") . "." . $fileExtension;

        $targetDirectory = "uploads/" . $newFileName;
        if (!move_uploaded_file($_FILES["excel"]["tmp_name"], $targetDirectory)) {
            echo "Failed to move uploaded file.";
        }

        require "exelReader/excel_reader2.php";
        require "exelReader/SpreadsheetReader.php";

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $nom = $row[0];
            $prenom = $row[1];
            $n_insc = $row[2];
            $birthday = $row[3];
            $email = $row[4];
            $niveauName = $row[5];
            $specialityName = $row[6];

            // Rechercher l'ID de niveau
            $niveauQuery = mysqli_query($conn, "SELECT niveau_id FROM niveau WHERE nom_niveau ='$niveauName'");
            if ($niveauRow = mysqli_fetch_assoc($niveauQuery)) {
                $niveau = $niveauRow['niveau_id'];
            } else {
                echo "Niveau not found for name: $niveauName";
                continue;
            }

            // Rechercher l'ID de speciality
            $specialityQuery = mysqli_query($conn, "SELECT speciality_id FROM speciality WHERE nom_speciality ='$specialityName'");
            if ($specialityRow = mysqli_fetch_assoc($specialityQuery)) {
                $speciality = $specialityRow['speciality_id'];
            } else {
                echo "Speciality not found for name: $specialityName";
                continue;
            }

            // Insertion des données dans la table etudiant
            $insertQuery = "INSERT INTO `etudiant`(`nom_etudiant`, `prenom_etudiant`, `n_inscription_etudiant`, `birthday_etudiant`, `email_etudiant`, `niveau_id`, `speciality_id`) 
                            VALUES ('$nom','$prenom','$n_insc','$birthday','$email','$niveau','$speciality')";
            if (!mysqli_query($conn, $insertQuery)) {
                echo "Error: " . mysqli_error($conn);
            }
        }

        echo "
        <script>
        alert('Successfully Imported');
        document.location.href = '';
        </script>
        ";
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

                    <label for="type">Type:</label>
                    <select id="type" name="type">
                        <option value="enseignant">Enseignant</option>
                        <option value="chef_specialite">Chef_speciality</option>
                    </select><br><br>

                    <label for="Speciality">Speciality:</label>
                    <select id="Speciality" name="Speciality">
                        <?php foreach ($specialites as $specialite) : ?>
                            <option value="<?php echo $specialite['speciality_id']; ?>"><?php echo $specialite['nom_speciality']; ?></option>
                        <?php endforeach; ?>
                    </select><br><br>

                    <input type="submit" value="Add Professor">
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
                        $query = "SELECT title_theme FROM theme WHERE status = 'attribue' AND speciality_id = '$speciality_id'";
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