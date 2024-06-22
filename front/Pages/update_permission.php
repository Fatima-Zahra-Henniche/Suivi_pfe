<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['theme_id'])) {
        $theme_id = $_POST['theme_id'];

        $query = "UPDATE Theme SET permission = 'oui' WHERE theme_id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $theme_id);
            if ($stmt->execute()) {
                echo 'success';
            } else {
                echo 'error';
            }
            $stmt->close();
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
    $conn->close();
} else {
    echo 'error';
}
