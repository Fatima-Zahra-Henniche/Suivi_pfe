<?php require 'connect.php';

session_start();

if (isset($_SESSION['chef_id'])) {
    $chef_id = $_SESSION['chef_id'];
    $type = 'chef_specialite';
    $sql = "SELECT e.nom_enseignant, e.prenom_enseignant, e.speciality_id, s.nom_speciality, 'chef speciality' AS job FROM enseignant e join speciality s ON e.speciality_id = s.speciality_id WHERE e.type = ? AND e.enseignant_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $type, $chef_id); // Bind type as string (s) and ens_id as integer (i)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<div class='toolbar'>";
                echo "<span>" . $row["nom_enseignant"] . " " . $row["prenom_enseignant"] . "</span>";
                echo "<span>" . $row["job"] . " " . $row["nom_speciality"] . " </span>";
                echo "<span class='logout'><a href='logout.php'>Déconnexion</a></span>"; // Modified to French "Déconnexion"
                echo "</div>";
            }
        } else {
            echo "0 results";
        }

        $stmt->close();
    } else {
        echo "Failed to prepare the SQL statement: " . $conn->error;
    }

    // $conn->close();
} else {
    echo "Enseignant ID not set in session.";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Liste des etudiants</title>
    <style>
        .toolbar {
            display: grid;
            grid-template-columns: repeat(2, 1fr) auto;
            /* Updated to accommodate the logout button */
            align-items: center;
            background-color: #BED1FC;
            padding: 10px;
            position: fixed;
            width: 100%;
            height: 9%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Added box shadow for better visibility */
        }

        .toolbar .logout {
            justify-self: end;
            /* Aligns the logout button to the end of the grid */
        }

        .toolbar a {
            color: #333;
            /* Adjusted link color */
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
            margin-top: 60px;
            margin-left: 15px;
        }

        /* Set the width and height of the image */
        .image-container {
            text-align: center;
            /* Center the image horizontally */
        }

        .image-container img {
            width: 40%;
            /* Adjust as needed */
            height: 55%;
            margin: 0 auto;
            margin-top: 40px;
            /* Center the image horizontally */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>La liste des etudiants sans sujet :</h1>
    </div>

    </br>

    <?php
    require 'connect.php';

    $chef_id = $_SESSION['chef_id'];

    // Obtenir la spécialité du chef
    $chef_query = "SELECT speciality_id FROM enseignant WHERE enseignant_id = $chef_id";
    $chef_result = mysqli_query($conn, $chef_query);
    $chef_row = mysqli_fetch_assoc($chef_result);
    $chef_speciality_id = $chef_row['speciality_id'];

    // Sélectionner les étudiants de la même spécialité de la base de données
    $rows = mysqli_query($conn, "SELECT * FROM etudiant WHERE speciality_id = $chef_speciality_id AND status = 'pas_choisi'");
    if ($rows && mysqli_num_rows($rows) > 0) {
    ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>N_insc</th>
                    <th>Email</th>
                    <th>Date_naissance</th>
                </tr>
            </thead>
            <?php
            $i = 1;
            foreach ($rows as $row) :
            ?>
                <tbody>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $row['nom_etudiant']; ?></td>
                        <td><?php echo $row['prenom_etudiant']; ?></td>
                        <td><?php echo $row['n_inscription_etudiant']; ?></td>
                        <td><?php echo $row['email_etudiant']; ?></td>
                        <td><?php echo $row['birthday_etudiant']; ?></td>
                    </tr>
                </tbody>
            <?php endforeach; ?>
        </table>
    <?php
    } else {
        echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
    }
    ?>
</body>

</html>