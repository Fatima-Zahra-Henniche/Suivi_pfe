<?php require 'connect.php';

session_start();

$type = 'chef_specialite';
$sql = "SELECT nom_enseignant, prenom_enseignant, 'chef speciality' AS job FROM enseignant WHERE type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<div class='toolbar'>";
        echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>"; // Corrected here
        echo "<span>" . $row["job"] . "</span>";
        echo "<span>Les Niveaux</span>";
        echo "</div>";
    }
} else {
    echo "0 results";
}
// $conn->close();
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
    <!-- <div class="toolbar">
        <span>Nom Prenom</span>
        <span>job</span>
        <span>Les Niveaux</span>
    </div> -->
    <!-- Menu Logo -->
    <div class="vertical-toolbar1">
        <button><img src="../images/imag.jpg" alt="Logo" class="logo" style="width: 20%; height: 25px; background:none;"></button>
        <button onclick="document.getElementById('emailModal').style.display='block'"> Lancer la proposition </button>
        <button onclick="document.getElementById('studentModal').style.display='block'">Importer Des Etudiants</button>
        <button onclick="document.getElementById('teacherModal').style.display='block'">Importer Des Enseignants</button>
        <button><a href="ChefS_List_etu.php">Liste des etudiants</a></button>
        <button><a href="ChefS_List_ens.php">Liste des enseignants</a></button>
        <!--<button>Envoyer Formulaire</button>-->
    </div>

    <!-- Home -->
    <div class="Home">
        <button><a href="ChefS_Liste_Theme_nom_valide.php"> Themes non valide </a></button>
        <button>Liste des themes</button>
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

        $newFileName = date("Y.m.d") . " - " . date("h.i.s") . "." . $fileExtension;

        $targetDirectory = "uploads/" . $newFileName;
        if (!move_uploaded_file($_FILES["excel"]["tmp_name"], $targetDirectory)) {
            echo "Failed to move uploaded file.";
        }

        require "exelReader/excel_reader2.php";
        require "exelReader/SpreadsheetReader.php";

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $id = $row[0];
            $nom = $row[1];
            $prenom = $row[2];
            $n_insc = $row[3];
            $birthday = $row[4];
            $email = $row[5];
            $niveau = $row[6];
            mysqli_query($conn, "INSERT INTO `etudiant`(`etudiant_id`,`nom_etudiant`, `prenom_etudiant`, `n_inscription_etudiant`,`birthday_etudiant`, `email_etudiant`,`niveau_id`) VALUES ('$id','$nom','$prenom','$n_insc','$birthday','$email','$niveau')");
        }

        echo
        "
        <script>
        alert('Successfully Imported');
        document.location.href = '';
        </script>
        ";
    }
    ?>

    <!-- Send Email -->
    <div id="emailModal" class="model">
        <div class="model-contant">
            <span class="close" onclick="document.getElementById('emailModal').style.display='none'">X</span>
            <div class="contant">
                <p> Do you really want to start the propositions </p>
                <p> by click on the botton below you agree to send emails to the professors telling them that they can start posting there themes </p>
                <button id="sendEmailButton">Send Email to Enseignants</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#sendEmailButton").click(function() {
                $.ajax({
                    url: 'sendEmail.php',
                    type: 'POST',
                    success: function(response) {
                        alert(response);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Log any errors to console
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
                        <option value="chef_speciality">chef_specialite</option>
                    </select><br><br>

                    <label for="speciality">Speciality:</label>
                    <select id="speciality" name="speciality">
                        <option value="1">Systeme informatique</option>
                        <option value="2">Speciality 2</option>
                        <!-- Ajoutez d'autres options ici selon vos besoins -->
                    </select><br><br>

                    <input type="submit" value="Add Professor">
                </form>
            </div>
        </div>
    </div>

</body>

</html>
