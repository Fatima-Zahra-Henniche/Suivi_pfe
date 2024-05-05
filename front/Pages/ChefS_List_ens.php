<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des enseignants</title>
</head>

<body>
    <h1>Liste des enseignants</h1>
    <a href="ChefS_Add_etu.php">Ajouter un enseignant</a>
    <?php
    require 'connect.php';
    $rows = mysqli_query($conn, "SELECT * FROM enseignant");
    if ($rows) {
    ?>
        <table border="1">
            <tr>
                <th>Id</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>N_tel</th>
                <th>Edit</th>
            </tr>
            <?php
            $i = 1;
            foreach ($rows as $row) :
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['nom_enseignant']; ?></td>
                    <td><?php echo $row['prenom_enseignant']; ?></td>
                    <td><?php echo $row['email_enseignant']; ?></td>
                    <td><?php echo $row['N_telephone_enseignant']; ?></td>
                    <td>
                        <a href="ChefS_Edit_ens.php?id=<?php echo $row['id']; ?>">Modifier</a></br>
                        <a href="ChefS_Delete_ens.php?id=<?php echo $row['id']; ?>">Supprimer</a>
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