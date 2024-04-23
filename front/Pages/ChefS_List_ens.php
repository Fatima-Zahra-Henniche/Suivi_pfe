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
            <th>Email</th>
            <th>N_tel</th>
        </tr>
        <?php
        require 'connect.php';
        $i = 1;
        $row = mysqli_query($conn, "SELECT * FROM suivi_pfe");
        foreach ($row as $row) :
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['nom_enseignant']; ?></td>
                <td><?php echo $row['prenom_enseignant']; ?></td>
                <td><?php echo $row['email_enseignant']; ?></td>
                <td><?php echo $row['N_telephone_enseignant']; ?></td>
                <td>
                    <a href="ChefS_Edit_ens.php?id=<?php echo $row['id']; ?>">Modifier</a>
                    <a href="ChefS_Delete_ens.php?id=<?php echo $row['id']; ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>