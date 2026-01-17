<?php
session_start();
header('Content-Type: application/json');
require_once '../conf/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Add Expense
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if (!$amount || !$category_id || !$date) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, $amount, $date, $description]);
        echo json_encode(['success' => true, 'message' => 'Pengeluaran berhasil disimpan']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    // Get Stats and Recent Transactions
    try {
        // 1. Recent Transactions (Last 5)
        $stmt = $pdo->prepare("
            SELECT e.id, e.amount, e.date, e.description, c.name as category_name, c.icon 
            FROM expenses e 
            JOIN categories c ON e.category_id = c.id 
            WHERE e.user_id = ? 
            ORDER BY e.date DESC, e.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent = $stmt->fetchAll();

        // 2. Monthly Total
        $stmt = $pdo->prepare("
            SELECT SUM(amount) as total 
            FROM expenses 
            WHERE user_id = ? AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$user_id]);
        $monthly_total = $stmt->fetch()['total'] ?? 0;

        // 3. Category Breakdown (for Chart)
        $stmt = $pdo->prepare("
            SELECT c.name, SUM(e.amount) as total
            FROM expenses e
            JOIN categories c ON e.category_id = c.id
            WHERE e.user_id = ? AND MONTH(date) = MONTH(CURRENT_DATE())
            GROUP BY c.id
        ");
        $stmt->execute([$user_id]);
        $breakdown = $stmt->fetchAll();

        echo json_encode([
            'recent' => $recent,
            'monthly_total' => $monthly_total,
            'breakdown' => $breakdown
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}
?>