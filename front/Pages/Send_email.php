<?php
require 'connect.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$chef_id = $_SESSION['chef_id'];

// Retrieve the speciality_id and email of the chef from the database
$sql_chef = "SELECT speciality_id, email_enseignant FROM Enseignant WHERE enseignant_id = ? AND type = 'chef_specialite'";
$stmt_chef = $conn->prepare($sql_chef);
$stmt_chef->bind_param("i", $chef_id);
$stmt_chef->execute();
$result_chef = $stmt_chef->get_result();

if ($result_chef->num_rows > 0) {
    $row_chef = $result_chef->fetch_assoc();
    $chef_speciality_id = $row_chef['speciality_id'];
    $chef_email = $row_chef['email_enseignant'];

    // Retrieve the departement_id based on the speciality_id through the Filiere table
    $sql_departement = "SELECT d.departement_id, d.nom_departement FROM Speciality s 
                        JOIN Filieres f ON s.filiere_id = f.filiere_id
                        JOIN Departement d ON f.departement_id = d.departement_id
                        WHERE s.speciality_id = ?";
    $stmt_departement = $conn->prepare($sql_departement);
    $stmt_departement->bind_param("i", $chef_speciality_id);
    $stmt_departement->execute();
    $result_departement = $stmt_departement->get_result();

    if ($result_departement->num_rows > 0) {
        $row_departement = $result_departement->fetch_assoc();
        $departement_id = $row_departement['departement_id'];
        $departement_name = $row_departement['nom_departement'];

        // Retrieve email addresses of all enseignants of the same departement from the database
        $sql = "SELECT email_enseignant FROM Enseignant WHERE type = 'enseignant' AND departement_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $departement_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = 'fati982468ma021@gmail.com'; // SMTP username
                $mail->Password = 'fade kitq fcey nebm'; // SMTP password or app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom($chef_email, 'Chef Specialite');

                // Loop through each row and send email to enseignants
                while ($row = $result->fetch_assoc()) {
                    $to_email = $row["email_enseignant"];
                    $subject = "Proposing Themes";
                    $message = "You can start proposing themes for the departement " . $departement_name . ".";

                    // Add a recipient
                    $mail->addAddress($to_email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    // Send email
                    if ($mail->send()) {
                        echo "Email sent successfully to: " . $to_email . "<br>";
                    } else {
                        echo "Email sending failed to: " . $to_email . "<br>";
                    }

                    // Clear all recipients and attachments for the next iteration
                    $mail->clearAddresses();
                    $mail->clearAttachments();
                }
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "No enseignant found in the database for this departement.";
        }
    } else {
        echo "Departement not found.";
    }
} else {
    echo "Chef speciality not found.";
}

$conn->close();
