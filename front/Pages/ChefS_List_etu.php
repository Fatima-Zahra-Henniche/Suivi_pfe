<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des etudiants</title>
</head>

<body>
    <h1>Liste des etudiants</h1>
    <a href="ChefS_Add_etu.php">Ajouter un etudiant</a>
    <?php
    require 'connect.php';
    $rows = mysqli_query($conn, "SELECT * FROM etudiant");
    if ($rows) {
    ?>
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
                        <a href="ChefS_Edit_etu.php?id=<?php echo $row['id']; ?>">Modifier</a>
                        <a href="ChefS_Delete_etu.php?id=<?php echo $row['id']; ?>">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php
    } else {
        echo "Aucun résultat trouvé.";
    }
    ?>

    </table>
</body>

</html>