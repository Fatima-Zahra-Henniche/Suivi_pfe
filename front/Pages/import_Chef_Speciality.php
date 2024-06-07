<?php
// import_Chef_Speciality.php

// Include the database connection file
include('connect.php');

// Initialize an array to store error messages
$errors = array();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $lastName = htmlspecialchars(trim($_POST['lastName']), ENT_QUOTES);
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES);
    $N_tel = htmlspecialchars(trim($_POST['N_tel']), ENT_QUOTES);
    $speciality = htmlspecialchars(trim($_POST['speciality']), ENT_QUOTES);

    // Validate form inputs
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($lastName)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Un email valide est obligatoire.";
    }
    if (empty($N_tel)) {
        $errors[] = "Le numéro de téléphone est obligatoire.";
    }
    if (empty($speciality)) {
        $errors[] = "La spécialité est obligatoire.";
    }

    // If no errors, proceed to insert data into the database
    if (empty($errors)) {
        // Prepare an SQL statement to insert the data
        $sql = "INSERT INTO Enseignant (nom_enseignant, prenom_enseignant, email_enseignant, N_telephone_enseignant, type, speciality_id) VALUES (?, ?, ?, ?,'chef_specialite', ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $name, $lastName, $email, $N_tel, $speciality);
            if ($stmt->execute()) {
                // Redirect to a success page or display a success message
                echo "Chef specialty added successfully!";
            } else {
                echo "Erreur: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erreur de préparation: " . $conn->error;
        }
    } else {
        // Display validation errors
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }
}

// Close the database connection
$conn->close();
