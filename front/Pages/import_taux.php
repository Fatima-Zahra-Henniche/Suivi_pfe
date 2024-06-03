<?php
require 'connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $taux_memoire = $_POST['taux_memoire'];
    $taux_logiciel = $_POST['taux_logiciel'];
    $binome_id = $_POST['binome_id'];

    $query = "UPDATE binome SET taux_memoire = ?, taux_logiciel = ? WHERE binome_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ddi", $taux_memoire, $taux_logiciel, $binome_id);  // "ddi" stands for double, double, integer
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Taux ajouté avec succès";
        } else {
            echo "Erreur lors de l'ajout du taux";
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête";
    }

    $conn->close();
}
