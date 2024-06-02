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
    <title>Liste des enseignants</title>
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
            padding-top: 50px;
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

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Liste des enseignants</h1>
    </div>
    <?php
    require 'connect.php';
    $rows = mysqli_query($conn, "SELECT * FROM enseignant WHERE type = 'enseignant'");

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
        $enseignant_id = intval($_POST['enseignant_id']);
        $sql = "DELETE FROM enseignant WHERE enseignant_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $enseignant_id);
        $stmt->execute();
        $stmt->close();
        header("Location: ChefS_List_ens.php");
        exit();
    }

    if ($rows) {
    ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Email</th>
                    <th>N_tel</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <?php
            $i = 1;
            foreach ($rows as $row) :
            ?>
                <tbody>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['nom_enseignant']); ?></td>
                        <td><?php echo htmlspecialchars($row['prenom_enseignant']); ?></td>
                        <td><?php echo htmlspecialchars($row['email_enseignant']); ?></td>
                        <td><?php echo htmlspecialchars($row['N_telephone_enseignant']); ?></td>
                        <td>
                            <form class="delete-form" action="ChefS_List_ens.php" method="post">
                                <input type="hidden" name="enseignant_id" value="<?php echo $row['enseignant_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="button" onclick="openModal(this)" class="btn btn-light mb-2">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            <?php endforeach; ?>
        </table>
    <?php
    } else {
        echo "<div class=\"image-container\"><img src=\"../images/no_result.png\" alt=\"No results image\"></div>";
    }
    ?>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p>Voulez-vous vraiment supprimer cet enseignant?</p>
            <div class="modal-footer">
                <button type="button" id="confirmDelete">Delete</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentForm;

        function openModal(button) {
            currentForm = button.closest('form');
            document.getElementById('myModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('myModal').style.display = "none";
        }

        document.getElementById('confirmDelete').onclick = function() {
            currentForm.submit();
        };

        // Close the modal if the user clicks anywhere outside of the modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('myModal')) {
                closeModal();
            }
        }
    </script>
</body>

</html>