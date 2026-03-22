<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// ============================================================
//  Spendwise — Stats & Reports API
//  GET /api/stats.php?type=summary     => totals + regret meter
//  GET /api/stats.php?type=monthly     => per-month breakdown
//  GET /api/stats.php?type=categories  => per-category breakdown
// ============================================================

require_once __DIR__ . '/../helpers.php';
setCorsHeaders();

$auth   = requireAuth();
$userId = (int) $auth['user_id'];
$db     = getDB();
$type   = $_GET['type'] ?? 'summary';

// SUMMARY — overall totals + regret meter data
if ($type === 'summary') {
    $stmt = $db->prepare(
        'SELECT
            COUNT(*)                                      AS total_count,
            COALESCE(SUM(amount), 0)                      AS total_amount,
            COALESCE(SUM(CASE WHEN feeling="happy"   THEN amount ELSE 0 END), 0) AS happy_amount,
            COALESCE(SUM(CASE WHEN feeling="neutral" THEN amount ELSE 0 END), 0) AS neutral_amount,
            COALESCE(SUM(CASE WHEN feeling="regret"  THEN amount ELSE 0 END), 0) AS regret_amount
         FROM expenses WHERE user_id = ?'
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    $total      = (float)$row['total_amount'];
    $regretPct  = $total > 0 ? round(($row['regret_amount'] / $total) * 100) : 0;

    sendSuccess([
        'summary' => [
            'total_count'    => (int)$row['total_count'],
            'total_amount'   => $total,
            'happy_amount'   => (float)$row['happy_amount'],
            'neutral_amount' => (float)$row['neutral_amount'],
            'regret_amount'  => (float)$row['regret_amount'],
            'regret_percent' => $regretPct,
        ]
    ]);
}

// MONTHLY — breakdown by month
if ($type === 'monthly') {
    $stmt = $db->prepare(
        'SELECT
            DATE_FORMAT(expense_date, "%Y-%m")            AS month,
            COUNT(*)                                      AS count,
            COALESCE(SUM(amount), 0)                      AS total,
            COALESCE(SUM(CASE WHEN feeling="happy"   THEN amount ELSE 0 END), 0) AS happy,
            COALESCE(SUM(CASE WHEN feeling="neutral" THEN amount ELSE 0 END), 0) AS neutral,
            COALESCE(SUM(CASE WHEN feeling="regret"  THEN amount ELSE 0 END), 0) AS regret
         FROM expenses WHERE user_id = ?
         GROUP BY month ORDER BY month DESC'
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['total']   = (float)$r['total'];
        $r['happy']   = (float)$r['happy'];
        $r['neutral'] = (float)$r['neutral'];
        $r['regret']  = (float)$r['regret'];
        $r['count']   = (int)$r['count'];
    }
    sendSuccess(['monthly' => $rows]);
}

// CATEGORIES — breakdown by category (optionally filtered by month)
if ($type === 'categories') {
    $params = [$userId];
    $extra  = '';
    if (!empty($_GET['month'])) {
        $extra    = "AND DATE_FORMAT(expense_date,'%Y-%m') = ?";
        $params[] = $_GET['month'];
    }

    $stmt = $db->prepare(
        "SELECT
            category,
            COALESCE(SUM(amount), 0) AS total,
            COUNT(*) AS count,
            COALESCE(SUM(CASE WHEN feeling='happy'   THEN amount ELSE 0 END),0) AS happy,
            COALESCE(SUM(CASE WHEN feeling='neutral' THEN amount ELSE 0 END),0) AS neutral,
            COALESCE(SUM(CASE WHEN feeling='regret'  THEN amount ELSE 0 END),0) AS regret
         FROM expenses
         WHERE user_id = ? $extra
         GROUP BY category ORDER BY total DESC"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['total']   = (float)$r['total'];
        $r['happy']   = (float)$r['happy'];
        $r['neutral'] = (float)$r['neutral'];
        $r['regret']  = (float)$r['regret'];
        $r['count']   = (int)$r['count'];
    }
    sendSuccess(['categories' => $rows]);
}

sendError('Invalid stats type.', 400);
