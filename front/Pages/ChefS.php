<?php require 'connect.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="../Styles/ChefS.css">
</head>

<body>
    <div class="toolbar">
        <span>Nom Prenom</span>
        <span>job</span>
        <span>Niveaux</span>
    </div>
    <!-- Menu Logo -->
    <div class="vertical-toolbar1">
        <button><img src="../images/imag.jpg" alt="Logo" class="logo" style="width: 20%; height: 25px; background:none;"></button>
        <button> Lancer la proposition </button>
        <button onclick="document.getElementById('teacherModal').style.display='block'">Importer</button>
        <button><a href="ChefS_List_etu.php" class="button-link">Liste des etudiants</a></button>
        <button><a href="ChefS_List_ens.php" class="button-link">Liste des enseignants</a></button>
        <button>Envoyer Formulaire</button>
        <button>informer</button>
    </div>
    <!-- Menu Logo -->
    <div id="teacherModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('teacherModal').style.display='none'">X</span>
            <div class="container">
                <h2>Upload an EXEL file please</h2>
                <form action="connect.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="excel" required value="">
                    <button type="submit" name="import">Import</button>
                </form>
            </div>
        </div>
    </div>
    <!--
    <div>
        <p> Do you really want to start the propositions </p>
        <p> by click on the botton below you agree to send emails to the professors telling them that they can start posting there themes </p>
        <button id="sendEmailButton">Send Email to Enseignants</button>
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
 -->
    <?php
    if (isset($_POST['import'])) {
        $fileName = $_FILES["exel"]["name"];
        $fileExtension = explode('.', $fileName);
        $fileExtension = strtolower(end($fileExtension));

        $newFileName = date("Y.m.d") . " - " . date("h.i.s") . "." . $fileExtension;

        $targetDirectory = "uploads/" . $newFileName;
        move_uploaded_file($_FILES["exel"]["tmp_name"], $targetDirectory);

        error_reporting(0);
        ini_set('display_errors', 0);

        require "exelReader/excel_reader2.php";
        require "exelReader/SpreadsheetReader.php";

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $name = $filesop[0];
            $prenom = $filesop[1];
            $n_ins = $filesop[2];
            $email = $filesop[3];
            mysqli_query($conn, "INSERT INTO `etudiant`(`nom_etudiant`, `prenom_etudiant`, `n_inscription_etudiant`, `email_etudiant`) VALUES ('$name','$prenom','$n_ins','$email')");
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

</body>

</html>