<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des etudiants</title>
</head>

<body>
    <table border="1">
        <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Prenom</th>
            <th>N_insc</th>
            <th>Email</th>
            <th>Date_naissance</th>
        </tr>
        <?php
        require 'connect.php';
        $i = 1;
        $row = mysqli_query($conn, "SELECT * FROM suivi_pfe");
        foreach ($row as $row) :
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['nom_etudiant']; ?></td>
                <td><?php echo $row['prenom_etudiant']; ?></td>
                <td><?php echo $row['n_inscription_etudiant']; ?></td>
                <td><?php echo $row['email_etudiant']; ?></td>
                <td><?php echo $row['birthday_etudiant']; ?></td>
                <td>
                    <a href="ChefS_Edit_etu.php?id=<?php echo $row['id']; ?>">Modifier</a>
                    <a href="ChefS_Delete_etu.php?id=<?php echo $row['id']; ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>