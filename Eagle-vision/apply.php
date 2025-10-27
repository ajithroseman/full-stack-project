<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $job = isset($_POST['job']) ? $_POST['job'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$experienceType = isset($_POST['experienceType']) ? $_POST['experienceType'] : '';
$experience = isset($_POST['experience']) ? $_POST['experience'] : '';
$resume = isset($_FILES['resume']) ? $_FILES['resume'] : null;

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'eaglevisiontechnologyngl@gmail.com'; // ⬅️ your Gmail address
        $mail->Password = 'app password';   // ⬅️ your Gmail App Password (not your normal password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('yourgmail@gmail.com', 'Eagle Vision Website'); // sender name
        $mail->addAddress('yourgmail@gmail.com', 'HR Department');     // recipient (can be same email)

        // Attach the resume if uploaded
        if ($resume && $resume['error'] === UPLOAD_ERR_OK) {
            $mail->addAttachment($resume['tmp_name'], $resume['name']);
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "New Job Application for: $job";
        $mail->Body = "
            <h3>New Application Received</h3>
            <p><strong>Job Title:</strong> $job</p>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Experience Type:</strong> $experienceType</p>
            " . ($experienceType === 'experienced' ? "<p><strong>Years of Experience:</strong> $experience</p>" : "") . "
        ";

        // Send email
        $mail->send();
        echo "<script>alert('Application submitted successfully!'); window.location.href='index.html';</script>";

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
