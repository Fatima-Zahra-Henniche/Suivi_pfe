<?php
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $theme_title = $_POST['theme'];
    $jury1_name = $_POST['jury1'];
    $jury2_name = $_POST['jury2'];
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $salle = $_POST['salle'];

    // Affichage des valeurs récupérées
    echo "Theme Title: " . htmlspecialchars($theme_title) . "<br>";
    echo "Jury 1 Name: " . htmlspecialchars($jury1_name) . "<br>";
    echo "Jury 2 Name: " . htmlspecialchars($jury2_name) . "<br>";
    echo "Date: " . htmlspecialchars($date) . "<br>";
    echo "Heure: " . htmlspecialchars($heure) . "<br>";
    echo "Salle: " . htmlspecialchars($salle) . "<br>";

    // Rechercher l'ID du thème à partir de son titre
    $query = "SELECT theme_id FROM theme WHERE title_theme = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $theme_title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $theme_id = $row['theme_id'];
        echo "Theme ID: " . $theme_id . "<br>";
    } else {
        // Gérer le cas où aucun thème n'est trouvé pour le titre donné
        echo "Aucun thème trouvé pour le titre sélectionné.";
        exit;
    }

    // Rechercher l'ID des jurys à partir de leurs noms complets
    $query = "SELECT enseignant_id FROM enseignant WHERE CONCAT(nom_enseignant, ' ', prenom_enseignant) = ?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("s", $jury1_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $jury1_id = $row['enseignant_id'];
        echo "Jury 1 ID: " . $jury1_id . "<br>";
    } else {
        // Gérer le cas où aucun enseignant n'est trouvé pour le nom donné
        echo "Aucun enseignant trouvé pour le nom de Jury 1.";
        exit;
    }

    $stmt->bind_param("s", $jury2_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $jury2_id = $row['enseignant_id'];
        echo "Jury 2 ID: " . $jury2_id . "<br>";
    } else {
        // Gérer le cas où aucun enseignant n'est trouvé pour le nom donné
        echo "Aucun enseignant trouvé pour le nom de Jury 2.";
        exit;
    }

    // Récupération de l'enseignant et du binôme associés au thème
    $query = "SELECT enseignant_id, binome_id FROM binome WHERE theme_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $enseignant_id = $row['enseignant_id'];
        $binome_id = $row['binome_id'];
        echo "Enseignant ID: " . $enseignant_id . "<br>";
        echo "Binome ID: " . $binome_id . "<br>";
    } else {
        // Gérer le cas où aucun binôme n'est trouvé pour le thème
        echo "Aucun binôme trouvé pour le thème sélectionné.";
        exit;
    }

    // Préparation de la requête SQL pour l'insertion
    $sql = "INSERT INTO planning (date_planning, heure_debut, salle, jury_01, jury_02, theme_id, enseignant_id, binome_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Erreur de préparation de la requête : ' . $conn->error);
    }

    // Liaison des variables de la requête préparée aux variables PHP
    $stmt->bind_param("sssiiiii", $date, $heure, $salle, $jury1_id, $jury2_id, $theme_id, $enseignant_id, $binome_id);

    // Exécution de la requête
    if ($stmt->execute()) {
        // Mise à jour du statut du thème à "termine"
        $update_query = "UPDATE theme SET status = 'termine' WHERE theme_id = ?";
        $update_stmt = $conn->prepare($update_query);
        if ($update_stmt === false) {
            die('Erreur de préparation de la requête de mise à jour : ' . $conn->error);
        }
        $update_stmt->bind_param("i", $theme_id);
        if ($update_stmt->execute()) {
            echo "Statut du thème mis à jour avec succès.<br>";
        } else {
            echo "Erreur lors de la mise à jour du statut du thème : " . $update_stmt->error;
        }

        header('Location: ../Pages/ChefS.php');
    } else {
        echo 'Erreur lors de l\'insertion : ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
