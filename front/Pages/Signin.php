<?php
session_start(); // Start or resume a session

// Connect to MySQL database
require 'connect.php';

// Retrieve form data (sanitize inputs to prevent SQL injection)
$username = $conn->real_escape_string($_POST['username']);
$password = $conn->real_escape_string($_POST['password']); // Assuming birthday is sent as password

// Check if the user is an enseignant
$sql_enseignant = "SELECT * FROM enseignant WHERE email_enseignant=? AND type='enseignant'";
$stmt_enseignant = $conn->prepare($sql_enseignant);
$stmt_enseignant->bind_param("s", $username);
$stmt_enseignant->execute();
$result_enseignant = $stmt_enseignant->get_result();

if ($result_enseignant->num_rows > 0) {
    $row = $result_enseignant->fetch_assoc();
    $storedTel = $row['N_telephone_enseignant'];
    if ($password === $storedTel) {
        // User is an enseignant, redirect to ens.php
        $_SESSION['ens_id'] = $row['enseignant_id']; // Store enseignant_id in session
        header("Location: ens.php");
        exit(); // Ensure no more output is sent
    }
}

// Check if the user is a chef_specialite
$sql_chef = "SELECT * FROM enseignant WHERE email_enseignant=? AND type='chef_specialite'";
$stmt_chef = $conn->prepare($sql_chef);
$stmt_chef->bind_param("s", $username);
$stmt_chef->execute();
$result_chef = $stmt_chef->get_result();

if ($result_chef->num_rows > 0) {
    $row = $result_chef->fetch_assoc();
    $storedTele = $row['N_telephone_enseignant'];
    if ($password === $storedTele) {
        // User is a chef_specialite, redirect to ChefS.php
        header("Location: ChefS.php");
        exit(); // Ensure no more output is sent
    }
}

// Close prepared statements for enseignant and chef_specialite checks
$stmt_enseignant->close();
$stmt_chef->close();

// If not enseignant or chef_specialite, then check if the user is an etudiant
$sql = "SELECT * FROM etudiant WHERE n_inscription_etudiant=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $storedBirthday = $row['birthday_etudiant']; // Assuming 'birthday' is the column name

    // Compare the entered birthday with the stored birthday
    if ($password === $storedBirthday) {
        // Authentication successful
        $_SESSION['etu_id'] = $row['etudiant_id']; // Store etudiant_id in session
        header("Location: etudiant.php");
        exit();
    } else {
        $_SESSION['username'] = $username; // Store username in session
        echo "Invalid password for etudiant"; // Notify that the password is incorrect for etudiant
    }
} else {
    // User not found
    echo "Invalid username or password"; // Notify that the username or password is invalid
}

// Close prepared statement and database connection for etudiant check
$stmt->close();
$conn->close();
