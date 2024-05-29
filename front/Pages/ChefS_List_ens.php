<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des enseignants</title>
    <style>
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
    <h1>Liste des enseignants</h1>
    <?php
    require 'connect.php';
    $rows = mysqli_query($conn, "SELECT * FROM enseignant");

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
        <table border="1">
            <tr>
                <th>Id</th>
                <th>Nom</th>
                <th>Prenom</th>
                <th>Email</th>
                <th>N_tel</th>
                <th>Delete</th>
            </tr>
            <?php
            $i = 1;
            foreach ($rows as $row) :
            ?>
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
                            <button type="button" onclick="openModal(this)">Delete</button>
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