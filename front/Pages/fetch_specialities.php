<?php
session_start();
include 'connect.php'; // Include your database connection script

if (!isset($_SESSION['ens_id'])) {
    echo json_encode(['error' => 'No enseignant ID found in session.']);
    exit;
}

$ens_id = $_SESSION['ens_id'];

// Fetch the enseignant's speciality_id
$query = "SELECT speciality_id FROM Enseignant WHERE enseignant_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ens_id);
$stmt->execute();
$stmt->bind_result($speciality_id);
$stmt->fetch();
$stmt->close();

// Check if speciality_id is valid
if (!$speciality_id) {
    echo json_encode(['error' => 'No speciality found for the enseignant.']);
    exit;
}

// Fetch the filiere_id associated with the speciality_id
$query = "SELECT filiere_id FROM Speciality WHERE speciality_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $speciality_id);
$stmt->execute();
$stmt->bind_result($filiere_id);
$stmt->fetch();
$stmt->close();

// Check if filiere_id is valid
if (!$filiere_id) {
    echo json_encode(['error' => 'No filiere found for the speciality.']);
    exit;
}

// Fetch all specialities for the filiere_id
$query = "SELECT speciality_id, nom_speciality FROM Speciality WHERE niveau_id IN (SELECT niveau_id FROM Niveau WHERE filiere_id = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $filiere_id);
$stmt->execute();
$result = $stmt->get_result();

$specialities = [];
while ($row = $result->fetch_assoc()) {
    $specialities[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json'); // Set response header
echo json_encode($specialities);
