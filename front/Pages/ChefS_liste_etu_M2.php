<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des etudiants</title>
</head>

<body>
    <h1>Liste des etudiants</h1>

    <!-- Formulaire pour supprimer tous les étudiants -->
    <form method="POST" action="">
        <button type="submit" name="delete_all">Supprimer tous les étudiants</button>
    </form>

    <?php
    require 'connect.php';

    // Supprimer un étudiant si une requête de suppression individuelle est reçue
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $delete_query = "DELETE FROM etudiant WHERE etudiant_id = $delete_id";
        mysqli_query($conn, $delete_query);
    }

    // Supprimer tous les étudiants si une requête de suppression globale est reçue
    if (isset($_POST['delete_all'])) {
        $delete_all_query = "DELETE FROM etudiant";
        mysqli_query($conn, $delete_all_query);
    }

    // Sélectionner les étudiants de la base de données
    $rows = mysqli_query($conn, "SELECT * FROM etudiant WHERE niveau_id = 2");
    if ($rows && mysqli_num_rows($rows) > 0) {
    ?>
        <table border="1">
            <tr>
                <th>Id</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>N_insc</th>
                <th>Email</th>
                <th>Date_naissance</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            foreach ($rows as $row) :
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['nom_etudiant']; ?></td>
                    <td><?php echo $row['prenom_etudiant']; ?></td>
                    <td><?php echo $row['n_inscription_etudiant']; ?></td>
                    <td><?php echo $row['email_etudiant']; ?></td>
                    <td><?php echo $row['birthday_etudiant']; ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="delete_id" value="<?php echo $row['etudiant_id']; ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php
    } else {
        echo "Aucun résultat trouvé.";
    }
    ?>
</body>

</html>