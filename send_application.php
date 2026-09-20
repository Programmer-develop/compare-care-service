<?php
// Include PHPMailer files
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/html; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

// Helper to get POST safely
function get_post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Collect and sanitize form inputs
$organization = htmlspecialchars(get_post('organization'));
$name = htmlspecialchars(get_post('name'));
$email = filter_var(get_post('email'), FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(get_post('phone'));
$message_text = htmlspecialchars(get_post('message'));

// Basic validation
$errors = [];
if ($name === '') $errors[] = "Name is required.";
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if ($phone === '') $errors[] = "Phone number is required.";
if ($message_text === '') $errors[] = "Message is required.";

if (!empty($errors)) {
    // You may want to redirect back with errors instead
    echo "<h3>There were problems with your submission:</h3><ul>";
    foreach ($errors as $err) {
        echo "<li>" . htmlspecialchars($err) . "</li>";
    }
    echo "</ul><p><a href='javascript:history.back()'>Go back</a></p>";
    exit;
}

/*
 * SMTP credentials
 *
 * IMPORTANT:
 * - It's strongly recommended to move the credentials to environment variables on the server:
 *   e.g. put these in the environment and read via getenv('MAIL_USER') / getenv('MAIL_PASS')
 *
 * For quick testing you can leave the hardcoded values below — but remove them before production.
 */

// Preferred: read from environment variables (uncomment when using env vars)
//$mail_user = getenv('MAIL_USER');
//$mail_pass = getenv('MAIL_PASS');

// Fallback / immediate-use credentials (you provided these)
// If you prefer to use environment variables, replace the values here with the getenv(...) calls above.
$mail_user = 'careruk24@gmail.com';
$mail_pass = 'lciu iyhd wpan fizc'; // <-- app password (keep secure!)

$to_email = 'programmerecare@gmail.com';
$to_name = 'Programmer Ecare';

try {
    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $mail_user;
    $mail->Password = $mail_pass;
    // Use TLS on port 587 (recommended). If you prefer SSL port 465, change SMTPSecure to 'ssl' and Port to 465.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // From and Reply-To
    $mail->setFrom($mail_user, 'Excellent Care (Applications)');
    // Set Reply-To to the applicant's email so the recipient can reply easily
    $mail->addReplyTo($email, $name);

    // Recipient
    $mail->addAddress($to_email, $to_name);

    // Subject and body
    $subject = "New Application from {$name} for " . ($organization ?: 'a provider');
    $mail->Subject = $subject;

    // Build a nicely formatted HTML body
    $htmlBody = "<h2>New Provider Application</h2>";
    $htmlBody .= "<p><strong>Organization:</strong> " . ($organization ?: 'N/A') . "</p>";
    $htmlBody .= "<p><strong>Applicant Name:</strong> " . $name . "</p>";
    $htmlBody .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $htmlBody .= "<p><strong>Phone:</strong> " . $phone . "</p>";
    $htmlBody .= "<p><strong>Message:</strong><br>" . nl2br($message_text) . "</p>";
    $htmlBody .= "<hr>";
    $htmlBody .= "<p>Sent from the Care Providers site application form.</p>";

    $plainBody = "New Provider Application\n\n";
    $plainBody .= "Organization: " . ($organization ?: 'N/A') . "\n";
    $plainBody .= "Applicant Name: " . $name . "\n";
    $plainBody .= "Email: " . $email . "\n";
    $plainBody .= "Phone: " . $phone . "\n\n";
    $plainBody .= "Message:\n" . $message_text . "\n\n";
    $plainBody .= "Sent from the Care Providers site application form.";

    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    $mail->AltBody = $plainBody;

    // (Optional) Attach uploaded files if your form allowed uploads (not included in current form)
    // Example:
    // if (!empty($_FILES['resume']['tmp_name'])) {
    //     $mail->addAttachment($_FILES['resume']['tmp_name'], $_FILES['resume']['name']);
    // }

    // Send
    if ($mail->send()) {
        // Successful send - you could redirect, show a success page, or return JSON
        echo "<h3>Application sent successfully.</h3>";
        echo "<p>Thank you, " . htmlspecialchars($name) . ". Your application has been emailed.</p>";
        echo "<p><a href='index.html'>Return to site</a></p>";
    } else {
        // PHPMailer usually throws exceptions; this block may not be reached often
        echo "<h3>Failed to send application.</h3>";
        echo "<p>Please try again later.</p>";
    }
} catch (Exception $e) {
    // On error, log it and show a friendly message
    // It's recommended to log $mail->ErrorInfo or $e->getMessage() to a file instead of echoing in production
    error_log("Mail error: " . $e->getMessage());
    echo "<h3>Error sending application.</h3>";
    echo "<p>There was a problem sending your application. Please try again later.</p>";
    // For debugging (remove in production):
    // echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}