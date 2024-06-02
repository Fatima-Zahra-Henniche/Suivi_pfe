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

    // Retrieve the name of the speciality
    $sql_speciality = "SELECT nom_speciality FROM Speciality WHERE speciality_id = ?";
    $stmt_speciality = $conn->prepare($sql_speciality);
    $stmt_speciality->bind_param("i", $chef_speciality_id);
    $stmt_speciality->execute();
    $result_speciality = $stmt_speciality->get_result();

    if ($result_speciality->num_rows > 0) {
        $row_speciality = $result_speciality->fetch_assoc();
        $speciality_name = $row_speciality['nom_speciality'];

        // Retrieve email addresses of all enseignants of the same speciality from the database
        $sql = "SELECT email_enseignant FROM Enseignant WHERE type = 'enseignant' AND speciality_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $chef_speciality_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = 'appelipad021@gmail.com'; // SMTP username
                $mail->Password = 'fatima982468zahra'; // SMTP password or app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom($chef_email, 'Chef Speciality');

                // Loop through each row and send email to enseignants
                while ($row = $result->fetch_assoc()) {
                    $to_email = $row["email_enseignant"];
                    $subject = "Proposing Themes";
                    $message = "You can start proposing themes for the speciality " . $speciality_name . ".";

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
            echo "No enseignant found in the database for this speciality.";
        }
    } else {
        echo "Speciality not found.";
    }
} else {
    echo "Chef speciality not found.";
}

$conn->close();
