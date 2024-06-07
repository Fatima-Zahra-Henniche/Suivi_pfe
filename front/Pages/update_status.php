<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $binome_id = $_POST['binome_id'];
    $delete_request = isset($_POST['delete_request']) && $_POST['delete_request'] == 'true';

    if ($delete_request) {
        // Perform the delete operation
        $delete_sql = "DELETE FROM binome WHERE binome_id = ?";
        $stmt = $conn->prepare($delete_sql);

        if ($stmt) {
            $stmt->bind_param("i", $binome_id);
            if ($stmt->execute()) {
                echo "Request deleted successfully.";
            } else {
                echo "Error deleting request: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Failed to prepare the SQL statement: " . $conn->error;
        }
    } else {
        // Handle the status update logic as before
        $theme_id = $_POST['theme_id'];
        $binome_status = $_POST['binome_status'];
        $theme_status = $_POST['theme_status'];
        $delete_others = $_POST['delete_others'] === 'true';

        // Update binome status
        $update_binome_sql = "UPDATE binome SET status = ? WHERE binome_id = ?";
        $stmt = $conn->prepare($update_binome_sql);

        if ($stmt) {
            $stmt->bind_param("si", $binome_status, $binome_id);
            $stmt->execute();
            $stmt->close();
        }

        // Update theme status if needed
        if ($theme_status) {
            $update_theme_sql = "UPDATE Theme SET status = ? WHERE theme_id = ?";
            $stmt = $conn->prepare($update_theme_sql);

            if ($stmt) {
                $stmt->bind_param("si", $theme_status, $theme_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Delete other requests for the same theme if needed
        if ($delete_others) {
            $delete_others_sql = "DELETE FROM binome WHERE theme_id = ? AND binome_id != ?";
            $stmt = $conn->prepare($delete_others_sql);

            if ($stmt) {
                $stmt->bind_param("ii", $theme_id, $binome_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        echo "Status updated successfully.";
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
