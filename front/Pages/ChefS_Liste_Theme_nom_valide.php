<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Themes non Valide</title>
</head>

<body>
    <h1>Liste des Themes non Valide</h1>
    <?php
    require 'connect.php';

    // Corrected SQL query
    $query = "SELECT * FROM theme WHERE status = 'non_valide'";
    $rows = mysqli_query($conn, $query);

    // Check if query was successful
    if ($rows) {
        if (mysqli_num_rows($rows) > 0) {
    ?>
            <table border="1">
                <tr>
                    <th>Id</th>
                    <th>Le Titre</th>
                    <th>accepte</th>
                    <th>refuse</th>
                </tr>
                <?php
                $i = 1;
                foreach ($rows as $row) :
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $row['title_theme']; ?></td>
                        <td><button>accepte</button></td>
                        <td><button>refuse</button></td>
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