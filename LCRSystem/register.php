<?php
// register.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/db.php';

function json_response($ok, $msg){ echo json_encode(['ok'=>$ok,'message'=>$msg]); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: createAccount.html'); exit;
}

$role   = $_POST['role']    ?? '';
$name   = trim($_POST['name'] ?? '');
$email  = strtolower(trim($_POST['email'] ?? ''));
$phone  = trim($_POST['phone'] ?? '');
$pass   = $_POST['password'] ?? '';
$confirm= $_POST['confirm']  ?? '';

if (!in_array($role, ['CITIZEN','STAFF'], true)) { die('Invalid role.'); }
if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { die('Invalid name or email.'); }
if (strlen($pass) < 8 || $pass !== $confirm) { die('Password invalid or does not match.'); }

try {
  $pdo = db(); // from config/db.php

  // 1) check duplicate
  $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  if ($stmt->fetch()) { die('Email already registered.'); }

  // 2) insert
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare('INSERT INTO users(name,email,phone,role,password_hash,is_active,created_at) VALUES(?,?,?,?,?,1,NOW())');
  $stmt->execute([$name,$email,$phone,$role,$hash]);

  // Optionally: auto-login or redirect
  if ($role === 'CITIZEN') {
    header('Location: clientLogin.html?registered=1'); // citizen/applicant login
  } else {
    header('Location: adminLogin.html?registered=1'); // staff/admin login
  }
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo 'Server error. Please try again later.';
}
