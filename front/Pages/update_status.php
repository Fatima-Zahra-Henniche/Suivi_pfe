<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $binomeId = $_POST['binome_id'];
    $deleteRequest = $_POST['delete_request'] === 'true';
    $etudiantStatus = isset($_POST['etudiant_status']) ? $_POST['etudiant_status'] : null;

    if ($deleteRequest) {
        // Handle the deletion request if needed
    } else {
        $themeId = $_POST['theme_id'];
        $binomeStatus = $_POST['binome_status'];
        $themeStatus = $_POST['theme_status'];
        $deleteOthers = $_POST['delete_others'] === 'true';

        $conn->begin_transaction();

        try {
            // Update binome status
            $stmt = $conn->prepare("UPDATE binome SET status = ? WHERE binome_id = ?");
            $stmt->bind_param("si", $binomeStatus, $binomeId);
            $stmt->execute();
            $stmt->close();

            // Update theme status
            if ($themeStatus !== null) {
                $stmt = $conn->prepare("UPDATE Theme SET status = ? WHERE theme_id = ?");
                $stmt->bind_param("si", $themeStatus, $themeId);
                $stmt->execute();
                $stmt->close();
            }

            // Update students' status
            if ($etudiantStatus !== null) {
                $stmt = $conn->prepare("UPDATE Etudiant SET status = ? WHERE etudiant_id IN (SELECT etudiant1_id FROM binome WHERE binome_id = ?) OR etudiant_id IN (SELECT etudiant2_id FROM binome WHERE binome_id = ?)");
                $stmt->bind_param("sii", $etudiantStatus, $binomeId, $binomeId);
                $stmt->execute();
                $stmt->close();
            }

            // Optionally delete other requests
            if ($deleteOthers) {
                $stmt = $conn->prepare("DELETE FROM binome WHERE theme_id = ? AND binome_id != ?");
                $stmt->bind_param("ii", $themeId, $binomeId);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            echo "Status updated successfully";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Failed to update status: " . $e->getMessage();
        }
    }
}
