<?php
// forgot_password_request.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: forgotPassword.html'); exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit('Please enter a valid email.');
}

try {
  $pdo = db();

  // Find user
  $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  // Always respond the same to avoid user enumeration
  $genericMsg = 'If that email exists, we sent a reset link. Please check your inbox.';

  if (!$user) {
    echo $genericMsg; exit;
  }

  // Create token & expiry
  $token = bin2hex(random_bytes(32)); // 64 chars
  $expires = (new DateTime('+60 minutes'))->format('Y-m-d H:i:s');

  // Upsert token for this email
  $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
  $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())');
  $stmt->execute([$email, $token, $expires]);

  // Build reset link
  $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
  $resetLink = rtrim($base, '/\\') . '/reset_password.php?token=' . urlencode($token) . '&email=' . urlencode($email);

  // TODO: Send mail using PHPMailer or mail() here.
  // For development, we echo the link:
  echo "<p>$genericMsg</p><p><small>Dev link: <a href=\"$resetLink\">$resetLink</a></small></p>";

} catch (Throwable $e) {
  http_response_code(500);
  echo 'Server error. Please try again later.';
}
