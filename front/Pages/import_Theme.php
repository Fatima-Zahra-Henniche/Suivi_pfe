<?php

// connexion à la base de données
require 'connect.php';

// Vérification de la méthode de requête HTTP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $name = $_POST['name'];
    $description = $_POST['description'];
    $objectives = $_POST['objectives'];
    $outils = $_POST['outils'];
    $connaissances = $_POST['connaissances'];
    $niveau = $_POST['niveau']; // ID du niveau sélectionné dans le menu déroulant
    $ens_id = $_POST['ens_id']; // ID de l'enseignant qui suggère le thème

    // Préparation de la requête SQL pour l'insertion
    $sql = "INSERT INTO theme (title_theme, description_theme, objectif_theme, outils_theme, connaissances_theme, niveau_id, enseignant_id) VALUES (:nom, :description, :objectives, :outils, :connaissances, :niveau_id, :ens_id)";

    $stmt = $pdo->prepare($sql);

    // Liaison des valeurs aux paramètres de la requête
    $stmt->bindParam(':nom', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':objectives', $objectives);
    $stmt->bindParam(':outils', $outils);
    $stmt->bindParam(':connaissances', $connaissances);
    $stmt->bindParam(':niveau_id', $niveau);
    $stmt->bindParam(':ens_id', $ens_id);

    // Exécution de la requête
    if ($stmt->execute()) {
        echo "Le Sujet est inserè avec succès.";
    } else {
        echo "Erreur lors de l'ajout du sujet.";
    }
}
