<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $binome_id = $_POST['binome_id'];
    $theme_id = $_POST['theme_id'];
    $binome_status = $_POST['binome_status'];
    $theme_status = $_POST['theme_status'];
    $delete_others = filter_var($_POST['delete_others'], FILTER_VALIDATE_BOOLEAN);

    // Update the status of the current binome
    $sql = "UPDATE binome SET status = ? WHERE binome_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $binome_status, $binome_id);
    $stmt->execute();
    $stmt->close();

    // Optionally update the theme status
    if ($theme_status !== null) {
        $sql = "UPDATE theme SET status = ? WHERE theme_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $theme_status, $theme_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete other requests with the same theme_id if delete_others is true
    if ($delete_others) {
        $sql = "DELETE FROM binome WHERE theme_id = ? AND binome_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $theme_id, $binome_id);
        $stmt->execute();
        $stmt->close();
    }

    echo "Status updated successfully";
}
