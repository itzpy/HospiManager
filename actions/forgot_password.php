<?php
session_start();
require_once '../db/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['errors']['general'] = "Invalid request method";
    echo json_encode($response);
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    $response['errors']['email'] = "Email is required";
    echo json_encode($response);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['errors']['email'] = "Invalid email format";
    echo json_encode($response);
    exit();
}

// Check user exists — prepared statement
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Don't reveal whether the email exists — security best practice
    $response['success'] = true;
    $response['message'] = "If that email is registered, a reset link has been sent.";
    echo json_encode($response);
    $stmt->close();
    exit();
}
$stmt->close();

// Generate token
$token  = bin2hex(random_bytes(50));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Delete any previous tokens for this email, then insert new one
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param("s", $email);
$del->execute();
$del->close();

$ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$ins->bind_param("sss", $email, $token, $expiry);
if (!$ins->execute()) {
    error_log("Password reset insert failed: " . $ins->error);
    $response['errors']['general'] = "Failed to process request. Please try again.";
    echo json_encode($response);
    $ins->close();
    exit();
}
$ins->close();

// Build reset URL dynamically
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$reset_url = $protocol . '://' . $host . '/view/reset_password.php?token=' . urlencode($token);

// Send email via PHPMailer + Brevo SMTP
$mail_sent = false;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request — Hospi Manager';
        $mail->isHTML(true);
        $mail->Body = "
            <p>You requested a password reset for your Hospi Manager account.</p>
            <p><a href='{$reset_url}'>Click here to reset your password</a></p>
            <p>This link expires in 1 hour.</p>
            <p>If you did not request this, ignore this email.</p>
        ";
        $mail->AltBody = "Reset your password here: {$reset_url}\n\nThis link expires in 1 hour.";

        $mail->send();
        $mail_sent = true;
    } catch (Exception $e) {
        error_log("Mailer error: " . $mail->ErrorInfo);
    }
} else {
    // Fallback: php mail() for local dev
    $subject  = "Password Reset Request";
    $message  = "Click the following link to reset your password: {$reset_url}\n\nThis link expires in 1 hour.";
    $headers  = "From: no-reply@hospimanager.to";
    $mail_sent = mail($email, $subject, $message, $headers);
}

if ($mail_sent) {
    $response['success'] = true;
    $response['message'] = "If that email is registered, a reset link has been sent.";
} else {
    $response['errors']['general'] = "Failed to send email. Please try again.";
}

echo json_encode($response);
