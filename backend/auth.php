<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// ============================================================
//  Spendwise — Auth API
//  POST /api/auth.php?action=register
//  POST /api/auth.php?action=login
//  GET  /api/auth.php?action=me          (requires token)
//  PUT  /api/auth.php?action=update      (requires token)
//  PUT  /api/auth.php?action=password    (requires token)
// ============================================================

require_once __DIR__ . '/../helpers.php';
setCorsHeaders();

$action = $_GET['action'] ?? '';

// ---- REGISTER ----
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body  = getBody();
    $name  = trim($body['name']  ?? '');
    $email = trim($body['email'] ?? '');
    $pass  = $body['password']   ?? '';

    if (!$name || !$email || !$pass) sendError('All fields are required.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) sendError('Invalid email address.');
    if (strlen($pass) < 6) sendError('Password must be at least 6 characters.');

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) sendError('Email is already registered.');

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);
    $userId = (int) $db->lastInsertId();

    $token = jwtCreate(['user_id' => $userId, 'name' => $name, 'email' => $email]);
    sendSuccess(['token' => $token, 'user' => ['id' => $userId, 'name' => $name, 'email' => $email]], 201);
}

// ---- LOGIN ----
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body  = getBody();
    $email = trim($body['email'] ?? '');
    $pass  = $body['password']   ?? '';

    if (!$email || !$pass) sendError('Email and password are required.');

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        sendError('Invalid email or password.', 401);
    }

    $token = jwtCreate(['user_id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]);
    sendSuccess(['token' => $token, 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']]]);
}

// ---- GET CURRENT USER ----
if ($action === 'me' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = requireAuth();
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, email, created_at FROM users WHERE id = ?');
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();
    if (!$user) sendError('User not found.', 404);
    sendSuccess(['user' => $user]);
}

// ---- UPDATE PROFILE ----
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $auth  = requireAuth();
    $body  = getBody();
    $name  = trim($body['name']  ?? '');
    $email = trim($body['email'] ?? '');

    if (!$name || !$email) sendError('Name and email are required.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) sendError('Invalid email address.');

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $stmt->execute([$email, $auth['user_id']]);
    if ($stmt->fetch()) sendError('Email is already in use.');

    $stmt = $db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute([$name, $email, $auth['user_id']]);

    $token = jwtCreate(['user_id' => $auth['user_id'], 'name' => $name, 'email' => $email]);
    sendSuccess(['token' => $token, 'user' => ['id' => $auth['user_id'], 'name' => $name, 'email' => $email]]);
}

// ---- CHANGE PASSWORD ----
if ($action === 'password' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $auth = requireAuth();
    $body = getBody();
    $curr = $body['current_password'] ?? '';
    $new  = $body['new_password']     ?? '';

    if (!$curr || !$new)   sendError('Current and new passwords are required.');
    if (strlen($new) < 6)  sendError('New password must be at least 6 characters.');

    $db   = getDB();
    $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$auth['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($curr, $user['password'])) sendError('Current password is incorrect.');

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hash, $auth['user_id']]);
    sendSuccess(['message' => 'Password changed successfully.']);
}

sendError('Invalid action or method.', 404);
