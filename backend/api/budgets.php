<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require_once __DIR__ . '/../helpers.php';
setCorsHeaders();

$auth   = requireAuth();
$userId = (int) $auth['user_id'];
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $ym   = date('Y-m');
    $stmt = $db->prepare('SELECT category, amount FROM budgets WHERE user_id = ? ORDER BY category');
    $stmt->execute([$userId]);
    $budgets = $stmt->fetchAll();

    foreach ($budgets as &$b) {
        $b['amount'] = (float)$b['amount'];
        // Calculate how much spent this month for this category
        $s = $db->prepare('SELECT COALESCE(SUM(amount),0) AS spent FROM expenses WHERE user_id=? AND category=? AND DATE_FORMAT(expense_date,"%Y-%m")=?');
        $s->execute([$userId, $b['category'], $ym]);
        $b['spent'] = (float)$s->fetch()['spent'];
        $b['percent'] = $b['amount'] > 0 ? min(round(($b['spent'] / $b['amount']) * 100), 100) : 0;
    }

    sendSuccess(['budgets' => $budgets]);
}

// SET / UPDATE BUDGET (upsert)
if ($method === 'POST') {
    $body     = getBody();
    $category = trim($body['category'] ?? '');
    $amount   = $body['amount'] ?? null;

    if (!$category) sendError('Please select a category.');
    if (!$amount || (float)$amount <= 0) sendError('Please enter a valid budget amount.');

    $stmt = $db->prepare('INSERT INTO budgets (user_id, category, amount) VALUES (?,?,?) ON DUPLICATE KEY UPDATE amount = VALUES(amount)');
    $stmt->execute([$userId, $category, (float)$amount]);
    sendSuccess(['message' => 'Budget saved for ' . $category . '.']);
}

// DELETE BUDGET
if ($method === 'DELETE') {
    $category = trim($_GET['category'] ?? '');
    if (!$category) sendError('Category is required.');
    $stmt = $db->prepare('DELETE FROM budgets WHERE user_id = ? AND category = ?');
    $stmt->execute([$userId, $category]);
    if ($stmt->rowCount() === 0) sendError('Budget not found.', 404);
    sendSuccess(['message' => 'Budget removed.']);
}
sendError('Invalid request.', 400);
