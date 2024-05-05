<?php
session_start(); // Start or resume a session

// Connect to MySQL database
require 'connect.php';

// Retrieve form data (sanitize inputs to prevent SQL injection)
$username = $conn->real_escape_string($_POST['username']);
$password = $conn->real_escape_string($_POST['password']); // Assuming birthday is sent as password

// SQL query using prepared statement to prevent SQL injection
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
        header("Location: etudiant.php");
        exit();
    } else if (password_verify($password, $row['N_telephone_enseignant'])) { // Added this line to check password using password_hash
        $_SESSION['username'] = $username; // Store username in session

        // Check if the user is an enseignant
        $sql_enseignant = "SELECT * FROM enseignant WHERE username=? AND type_enseignant='enseignant'";
        $stmt_enseignant = $conn->prepare($sql_enseignant);
        $stmt_enseignant->bind_param("s", $username);
        $stmt_enseignant->execute();
        $result_enseignant = $stmt_enseignant->get_result();

        if ($result_enseignant->num_rows > 0) {
            // User is an enseignant, redirect to ens.php
            header("Location: ens.php");
            exit(); // Ensure no more output is sent
        }

        // Check if the user is a chef_specialite
        $sql_chef = "SELECT * FROM enseignant WHERE username=? AND type_enseignant='chef_specialite'";
        $stmt_chef = $conn->prepare($sql_chef);
        $stmt_chef->bind_param("s", $username);
        $stmt_chef->execute();
        $result_chef = $stmt_chef->get_result();

        if ($result_chef->num_rows > 0) {
            // User is a chef_specialite, redirect to ChefS.php
            header("Location: ChefS.php");
            exit(); // Ensure no more output is sent
        }
    } else {
        // Invalid password
        echo "Invalid username or password";
    }
} else {
    // User not found
    echo "Invalid username or password";
}

// Close prepared statements and database connection
$stmt->close();
$stmt_enseignant->close();
$stmt_chef->close();
$conn->close();
