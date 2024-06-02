<?php
require 'connect.php';

session_start();

if (isset($_SESSION['ens_id'])) {
    $ens_id = $_SESSION['ens_id'];
    $type = 'enseignant';

    $sql = "SELECT nom_enseignant, prenom_enseignant, 'Enseignant' AS job FROM enseignant WHERE type = ? AND enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $ens_id); // Bind type as string (s) and ens_id as integer (i)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>" . $row["job"] . "</span>";
                echo "<span>Les Niveaux</span>";
                echo "</div>";
            }
        } else {
            echo "0 results";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement: " . $conn->error;
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
    <link rel="stylesheet" href="../Styles/ProfPage.css">
    <title>PFE</title>
</head>

<body>
    <!-- <div class="toolbar">
        <span>Nom Prenom</span>
        <span>job</span>
        <span>Les Niveaux</span>
    </div> -->
    <div>
        <button onclick="document.getElementById('ThemModal').style.display='block'">Add +</button>
        <button><a href="Liste_demande.php"> Liste des demandes </a></button>
        <button><a href="Liste_Theme_L3.php"> Liste des Themes L3</a></button>
        <button><a href="Liste_Theme_M2.php"> Liste des Themes M2</a></button>
    </div>

    <div id="ThemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('ThemModal').style.display='none'">X</span>
            <div class="container">
                <h2>Ajouter Un Sujet</h2>
                <form action="import_Theme.php" method="post">
                    <label for="name">Titre:</label>
                    <input type="text" id="name" name="name"><br><br>

                    <label for="description">Description:</label>
                    <input type="text" id="description" name="description"><br><br>

                    <label for="objectives">Objectives:</label>
                    <input type="text" id="objectives" name="objectives"><br><br>

                    <label for="outils">Les Outiles A Utiliser:</label>
                    <input type="text" id="outils" name="outils"><br><br>

                    <label for="connaissances">Les Connaissances:</label>
                    <input type="text" id="connaissances" name="connaissances"><br><br>

                    <label for="stage">Stage</label>
                    <select id="stage" name="stage">
                        <option value="1">oui</option>
                        <option value="0">non</option>
                    </select><br>

                    <label for="niveau">Niveau:</label>
                    <select id="niveau" name="niveau">
                        <option value="1">L3</option>
                        <option value="2">M2</option>
                    </select><br>

                    <label for="Speciality">Speciality:</label>
                    <select id="Speciality" name="Speciality">
                        <option value="1">SI</option>
                        <option value="2">IL</option>
                        <option value="3">ISIA</option>
                        <option value="4">RFIA</option>
                        <!-- Ajoutez d'autres options ici selon vos besoins -->
                    </select><br><br>

                    <!-- Hidden input for ens_id -->
                    <input type="hidden" id="ens_id" name="ens_id" value="<?php echo $_SESSION['ens_id']; ?>">

                    <input type="submit" value="Add Sujet">
                </form>
            </div>
        </div>
    </div>
</body>

</html>