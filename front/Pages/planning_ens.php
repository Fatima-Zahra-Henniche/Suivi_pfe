<?php
require 'connect.php';
session_start();

if (!isset($_SESSION['ens_id'])) {
    echo "Enseignant ID not set in session.";
    exit;
}

$ens_id = $_SESSION['ens_id'];
$type = 'enseignant';

// Fetch teacher's name and surname
$sql = "SELECT nom_enseignant, prenom_enseignant FROM Enseignant WHERE type = ? AND enseignant_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Failed to prepare the SQL statement: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("si", $type, $ens_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
    $teacher_name = htmlspecialchars($teacher["nom_enseignant"]) . " " . htmlspecialchars($teacher["prenom_enseignant"]);
} else {
    die("No results found.");
}

$stmt->close();

// Fetch the departement_id for the logged-in teacher
$sql = "SELECT departement_id FROM Enseignant WHERE enseignant_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Failed to prepare the SQL statement (departement_id): " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $ens_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $departement_id = $row['departement_id'];
} else {
    die("No departement found for this enseignant ID.");
}

$stmt->close();

// Fetch the filiere_id associated with the departement_id
$query = "SELECT filiere_id FROM Filieres WHERE departement_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Failed to prepare the SQL statement (filiere_id): " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $departement_id);
$stmt->execute();
$filiere_id_result = $stmt->get_result();

if ($filiere_id_result->num_rows > 0) {
    $filiere_row = $filiere_id_result->fetch_assoc();
    $filiere_id = $filiere_row['filiere_id'];
} else {
    die("No filiere found for this departement.");
}

$stmt->close();

// Fetch Speciality options for the same filiere
$query = "SELECT speciality_id, nom_speciality FROM Speciality WHERE filiere_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Failed to prepare the SQL statement (speciality): " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $filiere_id);
$stmt->execute();
$specialites_result = $stmt->get_result();

if ($specialites_result->num_rows > 0) {
    $specialites = $specialites_result->fetch_all(MYSQLI_ASSOC);
} else {
    die("No specialities found for this filiere.");
}

$stmt->close();

$planning_by_speciality = [];

foreach ($specialites as $specialite) {
    $speciality_id = $specialite['speciality_id'];
    $query = "SELECT p.date_planning, p.heure_debut, p.salle, e.nom_enseignant,
                    et1.nom_etudiant AS etudiant1_nom, et1.prenom_etudiant AS etudiant1_prenom,
                    et2.nom_etudiant AS etudiant2_nom, et2.prenom_etudiant AS etudiant2_prenom,
                    e1.nom_enseignant AS nom1_jury, e1.prenom_enseignant AS prenom1_jury,
                    e2.nom_enseignant AS nom2_jury, e2.prenom_enseignant AS prenom2_jury
                FROM planning p
                JOIN enseignant e ON p.enseignant_id = e.enseignant_id
                JOIN binome b ON p.theme_id = b.theme_id
                JOIN etudiant et1 ON b.etudiant1_id = et1.etudiant_id
                JOIN etudiant et2 ON b.etudiant2_id = et2.etudiant_id
                JOIN enseignant e1 ON p.jury_01 = e1.enseignant_id
                JOIN enseignant e2 ON p.jury_02 = e2.enseignant_id
                JOIN theme t ON p.theme_id = t.theme_id
                WHERE t.speciality_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Failed to prepare the SQL statement (planning): " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $speciality_id);
    $stmt->execute();
    $planning_result = $stmt->get_result();

    if ($planning_result->num_rows > 0) {
        $planning = $planning_result->fetch_all(MYSQLI_ASSOC);
        $planning_by_speciality[htmlspecialchars($specialite['nom_speciality'])] = $planning;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des planning attribue</title>
    <style>
        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            height: 6%;
            top: 0;
            left: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .toolbar .logout {
            justify-self: end;
            padding-right: 15px;
        }

        .toolbar a {
            color: #333;
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

        .container {
            padding-top: 70px;
            padding-left: 20px;
        }

        .image-container {
            text-align: center;
        }

        .image-container img {
            width: 35%;
            /* Adjust as needed */
            height: 60%;
            margin: 0 auto;
            /* Center the image horizontally */
        }

        .speciality-group {
            margin-bottom: 20px;
        }

        .speciality-group h2 {
            margin-bottom: 10px;
        }

        .theme-table {
            width: 100%;
            border-collapse: collapse;
        }

        .theme-table th,
        .theme-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .theme-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <span><?php echo $teacher_name; ?></span>
        <span>Enseignant</span>
        <span class="logout"><a href='logout.php'>Déconnexion</a></span>
    </div>
    <div class="container">
        <h1>Liste des planning attribue</h1>
    </div>
    <?php if (!empty($planning_by_speciality)) : ?>
        <?php foreach ($planning_by_speciality as $speciality => $planning) : ?>
            <div class="speciality-group">
                <h2><?php echo htmlspecialchars($speciality); ?></h2>
                <table class="theme-table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Etudiant</th>
                            <th>Enacadrant</th>
                            <th>jury</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Salle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planning as $index => $theme) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($theme['etudiant1_nom']) . " " . htmlspecialchars($theme['etudiant1_prenom']) . "<br>" . htmlspecialchars($theme['etudiant2_nom']) . " " . htmlspecialchars($theme['etudiant2_prenom']); ?></td>
                                <td><?php echo htmlspecialchars($theme['nom_enseignant']); ?></td>
                                <td><?php echo htmlspecialchars($theme['nom1_jury']) . " " . htmlspecialchars($theme['prenom1_jury']) . "<br>" . htmlspecialchars($theme['nom2_jury']) . " " . htmlspecialchars($theme['prenom2_jury']); ?></td>
                                <td><?php echo htmlspecialchars($theme['date_planning']); ?></td>
                                <td><?php echo htmlspecialchars($theme['heure_debut']); ?></td>
                                <td><?php echo htmlspecialchars($theme['salle']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="image-container">
            <img src="../images/no_result.png" alt="No results image">
        </div>
    <?php endif; ?>
</body>

</html>