<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// ============================================================
//  Spendwise — Expenses API
//  GET    /api/expenses.php            => list all expenses
//  POST   /api/expenses.php            => create expense
//  PUT    /api/expenses.php?id=X       => update expense
//  DELETE /api/expenses.php?id=X       => delete expense
// ============================================================

require_once __DIR__ . '/../helpers.php';
setCorsHeaders();

$auth   = requireAuth();
$userId = (int) $auth['user_id'];
$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// GET ALL EXPENSES
if ($method === 'GET') {
    $where  = ['e.user_id = ?'];
    $params = [$userId];

    if (!empty($_GET['feeling']))  { $where[] = 'e.feeling = ?';                            $params[] = $_GET['feeling']; }
    if (!empty($_GET['category'])) { $where[] = 'e.category = ?';                           $params[] = $_GET['category']; }
    if (!empty($_GET['month']))    { $where[] = 'DATE_FORMAT(e.expense_date,"%Y-%m") = ?';  $params[] = $_GET['month']; }

    $sql  = 'SELECT e.id, e.amount, e.category, e.description, e.feeling,
                    e.expense_date AS date, e.created_at
             FROM expenses e
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY e.expense_date DESC, e.created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();
    foreach ($expenses as &$exp) { $exp['amount'] = (float)$exp['amount']; $exp['id'] = (int)$exp['id']; }
    sendSuccess(['expenses' => $expenses]);
}

// CREATE EXPENSE
if ($method === 'POST') {
    $body        = getBody();
    $amount      = $body['amount']      ?? null;
    $category    = trim($body['category']    ?? '');
    $description = trim($body['description'] ?? '');
    $feeling     = $body['feeling']     ?? '';
    $date        = $body['date']        ?? '';

    if (!$amount || (float)$amount <= 0) sendError('Please enter a valid amount.');
    if (!$category) sendError('Please select a category.');
    if (!in_array($feeling, ['happy','neutral','regret'])) sendError('Invalid feeling value.');
    if (!$date || !strtotime($date)) sendError('Please enter a valid date.');

    $stmt = $db->prepare('INSERT INTO expenses (user_id, amount, category, description, feeling, expense_date) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$userId, (float)$amount, $category, $description, $feeling, $date]);
    $newId = (int) $db->lastInsertId();

    sendSuccess(['expense' => ['id' => $newId, 'amount' => (float)$amount, 'category' => $category, 'description' => $description, 'feeling' => $feeling, 'date' => $date]], 201);
}

// UPDATE EXPENSE
if ($method === 'PUT' && $id) {
    $stmt = $db->prepare('SELECT id FROM expenses WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) sendError('Expense not found.', 404);

    $body        = getBody();
    $amount      = $body['amount']      ?? null;
    $category    = trim($body['category']    ?? '');
    $description = trim($body['description'] ?? '');
    $feeling     = $body['feeling']     ?? '';
    $date        = $body['date']        ?? '';

    if (!$amount || (float)$amount <= 0) sendError('Please enter a valid amount.');
    if (!$category) sendError('Please select a category.');
    if (!in_array($feeling, ['happy','neutral','regret'])) sendError('Invalid feeling value.');
    if (!$date || !strtotime($date)) sendError('Please enter a valid date.');

    $stmt = $db->prepare('UPDATE expenses SET amount=?,category=?,description=?,feeling=?,expense_date=? WHERE id=? AND user_id=?');
    $stmt->execute([(float)$amount, $category, $description, $feeling, $date, $id, $userId]);
    sendSuccess(['message' => 'Expense updated.']);
}

// DELETE EXPENSE
if ($method === 'DELETE' && $id) {
    $stmt = $db->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    if ($stmt->rowCount() === 0) sendError('Expense not found.', 404);
    sendSuccess(['message' => 'Expense deleted.']);
}

sendError('Invalid request.', 400);
