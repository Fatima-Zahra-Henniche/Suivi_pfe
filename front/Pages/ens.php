<?php
require 'connect.php';

session_start();

if (isset($_SESSION['ens_id'])) {
    $ens_id = $_SESSION['ens_id'];
    $type = 'enseignant';

    $sql = "SELECT nom_enseignant, prenom_enseignant FROM enseignant WHERE type = ? AND enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $ens_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>Enseignant</span>";
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

    // Fetch the departement_id for the logged-in teacher
    $sql = "SELECT departement_id FROM enseignant WHERE enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    // Debugging line
    if (!$stmt) {
        die("Failed to prepare the SQL statement (departement_id): " . $conn->error);
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

        // Debugging line
        if (!$stmt) {
            die("Failed to prepare the SQL statement (filiere_id): " . $conn->error);
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

            // Debugging line
            if (!$stmt) {
                die("Failed to prepare the SQL statement (speciality): " . $conn->error);
            }

            $stmt->bind_param("i", $filiere_id);
            $stmt->execute();
            $specialites_result = $stmt->get_result();

            if ($specialites_result->num_rows > 0) {
                $specialites = $specialites_result->fetch_all(MYSQLI_ASSOC);
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
    <title>PFE</title>
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
            height: 5%;
            top: 0;
            left: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Added box shadow for better visibility */
        }

        .toolbar .logout {
            justify-self: end;
            padding-right: 15px;
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

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .contaner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #F1F8FF;
        }

        .content {
            background-color: #E4E4E4;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar button {
            background-color: #7D80C7;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar button:hover {
            background-color: #acaff1;
        }

        .sidebar button a {
            color: white;
            text-decoration: none;
        }

        .sidebar button a:hover {
            text-decoration: underline;
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
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #7D80C7;
            margin: 2% auto;
            /* تم تعديل هذه القيمة */
            padding: 20px;
            border: 2px #efeff2;
            width: 60%;
            border-radius: 10px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 5px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            /* Ajoute des transitions */
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #7D80C7;
            /* Change la couleur de la bordure au focus */
            box-shadow: 0 0 5px rgba(125, 128, 199, 0.5);
            /* Ajoute une ombre bleue au focus */
            outline: none;
            /* Supprime le contour par défaut */
        }


        input[type="submit"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 3px;
            background-color: #7D80C7;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #acaff1;
        }
    </style>
</head>

<body>
    <div class="contaner">
        <div class="content">
            <div class="sidebar">
                <button onclick="document.getElementById('ThemModal').style.display='block'">Ajouter Un Sujet</button>
                <button><a href="Liste_demande.php"> Liste des demandes </a></button>
                <button><a href="Liste_encadrement.php"> Liste des encadrements </a></button>
                <button><a href="Liste_Theme_nom_Attribue_ens.php"> Liste des Themes non Attribue </a></button>
                <button><a href="Liste_Theme_Attribue_ens.php"> Liste des Themes Attribue </a></button>
                <button><a href="planning_ens.php"> Liste De Planning </a></button>
            </div>
        </div>
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
                        <option value="oui">Oui</option>
                        <option value="non">Non</option>
                    </select></br></br>

                    <label for="Speciality">Speciality:</label>
                    <select id="Speciality" name="Speciality">
                        <?php if (isset($specialites)) : ?>
                            <?php foreach ($specialites as $specialite) : ?>
                                <option value="<?php echo $specialite['speciality_id']; ?>"><?php echo $specialite['nom_speciality']; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select></br></br>

                    <!-- Hidden input for ens_id -->
                    <input type="hidden" id="ens_id" name="ens_id" value="<?php echo $_SESSION['ens_id']; ?>">

                    <input type="submit" value="Add Sujet">
                </form>
            </div>
        </div>
    </div>
</body>

</html>