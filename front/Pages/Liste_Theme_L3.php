<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Themes Attribue L3</title>
</head>

<body>
    <h1>Liste des Themes Attribue L3</h1>
    <?php
    require 'connect.php';

    // Modifié la requête SQL pour inclure les statuts 'attribue'
    $query = "SELECT t.*, e.nom_enseignant,
                    et1.nom_etudiant AS etudiant1_nom, et1.prenom_etudiant AS etudiant1_prenom,
                    et2.nom_etudiant AS etudiant2_nom, et2.prenom_etudiant AS etudiant2_prenom
                FROM theme t
                JOIN enseignant e ON t.enseignant_id = e.enseignant_id
                JOIN binome b ON t.binome_id = b.binome_id
                JOIN etudiant et1 ON b.etudiant1_id = et1.etudiant_id
                JOIN etudiant et2 ON b.etudiant2_id = et2.etudiant_id
                WHERE t.status = 'attribue' AND t.niveau_id = 1";

    $rows = mysqli_query($conn, $query);

    // Vérifiez si la requête a réussi
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table border="1">
                <tr>
                    <th>Id</th>
                    <th>Le Titre</th>
                    <th>Encadrant</th>
                    <th>Details</th>
                    <th>Binome</th>
                </tr>
                <?php
                $i = 1;
                foreach ($rows as $row) :
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['title_theme']); ?></td>
                        <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                        <td><button>Details</button></td>
                        <td>
                            <?php echo htmlspecialchars($row['etudiant1_nom']); ?> . <?php echo htmlspecialchars($row['etudiant1_prenom']); ?><br>
                            <?php echo htmlspecialchars($row['etudiant2_nom']); ?> . <?php echo htmlspecialchars($row['etudiant2_prenom']); ?><br>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
    <?php
        } else {
            echo "Aucun résultat trouvé.";
        }
    } else {
        echo "Erreur dans la requête: " . mysqli_error($conn);
    }
    ?>
</body>

</html>