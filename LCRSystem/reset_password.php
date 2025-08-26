<?php
// reset_password.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $email = $_GET['email'] ?? '';
  $token = $_GET['token'] ?? '';
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LCR — Reset Password</title>
    <style>
      :root{ --blue-100:#D0E1F5; --blue-200:#A8BCDA; --blue-700:#415D8A; --white:#fff; --text:#1e293b; }
      html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;color:var(--text);
        background:linear-gradient(135deg,var(--blue-100),var(--white) 60%,var(--blue-200))}
      .container{min-height:100%;display:grid;place-items:center;padding:24px}
      .card{width:100%;max-width:480px;background:#fff;border-radius:20px;border:1px solid rgba(65,93,138,.08);
        box-shadow:0 10px 30px rgba(65,93,138,.18);padding:22px 24px}
      h2{color:var(--blue-700);margin:0 0 8px}
      label{display:block;margin:12px 0 6px 2px}
      input{width:100%;padding:12px 14px;border-radius:12px;border:1.2px solid #c4d4ea;background:#f7fbff}
      input:focus{border-color:var(--blue-700);box-shadow:0 0 0 4px rgba(65,93,138,.12);background:#fff}
      button{margin-top:16px;width:100%;border:none;cursor:pointer;font-weight:700;padding:12px 16px;border-radius:12px;color:#fff;
        background:linear-gradient(135deg,var(--blue-700),#2e4466)}
      .muted{text-align:center;font-size:.9rem;color:#586a86;margin-top:12px}
    </style>
  </head>
  <body>
    <div class="container">
      <div class="card">
        <h2>Set a new password</h2>
        <form method="post" action="reset_password.php">
          <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">

          <label for="password">New password</label>
          <input id="password" name="password" type="password" minlength="8" required placeholder="At least 8 characters">

          <label for="confirm">Confirm password</label>
          <input id="confirm" name="confirm" type="password" minlength="8" required placeholder="Re-type password">

          <button type="submit">Update password</button>
          <p class="muted"><a href="clientLogin.html">Back to sign in</a></p>
        </form>
      </div>
    </div>
    <script>
      // simple client-side check
      document.querySelector('form').addEventListener('submit', function(e){
        const p = document.getElementById('password').value;
        const c = document.getElementById('confirm').value;
        if (p !== c) { e.preventDefault(); alert('Passwords do not match.'); }
      });
    </script>
  </body>
  </html>
  <?php
  exit;
}

if ($method === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $token = $_POST['token'] ?? '';
  $pass  = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';

  if ($email === '' || $token === '' || strlen($pass) < 8 || $pass !== $confirm) {
    exit('Invalid request.');
  }

  try {
    $pdo = db();

    // verify token
    $stmt = $pdo->prepare('SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW() LIMIT 1');
    $stmt->execute([$email, $token]);
    $reset = $stmt->fetch();

    if (!$reset) { exit('The reset link is invalid or has expired.'); }

    // update password
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?')->execute([$hash, $email]);

    // delete token
    $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

    // success → send to login
    header('Location: clientLogin.html?reset=1');
    exit;

  } catch (Throwable $e) {
    http_response_code(500);
    echo 'Server error. Please try again later.';
  }
}
