<?php

// connexion à la base de données
require 'connect.php';

// Vérification de la méthode de requête HTTP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $name = $_POST['name'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $N_tel = $_POST['N_tel'];
    $type = $_POST['type']; // soit "enseignant" soit "chef_speciality"
    $speciality = $_POST['speciality']; // ID du speciality sélectionné dans le menu déroulant

    // Préparation de la requête SQL pour l'insertion
    $sql = "INSERT INTO enseignant (nom_enseignant, prenom_enseignant, email_enseignant, N_telephone_enseignant, type, speciality_id) 
            VALUES (:nom, :prenom, :email, :N_tel, :type, :speciality_id)";

    $stmt = $pdo->prepare($sql);

    // Liaison des valeurs aux paramètres de la requête
    $stmt->bindParam(':nom', $name);
    $stmt->bindParam(':prenom', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':N_tel', $N_tel);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':speciality_id', $speciality);

    // Exécution de la requête
    if ($stmt->execute()) {
        echo "Enseignant ajouté avec succès.";
    } else {
        echo "Erreur lors de l'ajout de l'enseignant.";
    }
}
