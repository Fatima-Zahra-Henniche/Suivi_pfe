<?php
session_start();

// connexion à la base de données
require 'connect.php';

// Vérification de la méthode de requête HTTP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $name = $_POST['name'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $N_tel = $_POST['N_tel'];

    // Récupération du speciality_id de la session
    $speciality_id = $_SESSION['Chef_speciality_id'];

    // Récupération du filiere_id à partir du speciality_id
    $stmt = $pdo->prepare("SELECT filiere_id FROM Speciality WHERE speciality_id = :speciality_id");
    $stmt->bindParam(':speciality_id', $speciality_id);
    $stmt->execute();
    $filiere = $stmt->fetch(PDO::FETCH_ASSOC);
    $filiere_id = $filiere['filiere_id'];

    // Récupération du departement_id à partir du filiere_id
    $stmt = $pdo->prepare("SELECT departement_id FROM Filieres WHERE filiere_id = :filiere_id");
    $stmt->bindParam(':filiere_id', $filiere_id);
    $stmt->execute();
    $departement = $stmt->fetch(PDO::FETCH_ASSOC);
    $departement_id = $departement['departement_id'];

    // Préparation de la requête SQL pour l'insertion
    $sql = "INSERT INTO Enseignant (nom_enseignant, prenom_enseignant, email_enseignant, N_telephone_enseignant, type, departement_id, speciality_id) 
            VALUES (:nom, :prenom, :email, :N_tel, 'enseignant', :departement_id, NULL)";

    $stmt = $pdo->prepare($sql);

    // Liaison des valeurs aux paramètres de la requête
    $stmt->bindParam(':nom', $name);
    $stmt->bindParam(':prenom', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':N_tel', $N_tel);
    $stmt->bindParam(':departement_id', $departement_id);

    // Exécution de la requête
    if ($stmt->execute()) {
        echo "Enseignant ajouté avec succès.";
    } else {
        echo "Erreur lors de l'ajout de l'enseignant.";
    }
}
