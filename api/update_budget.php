<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $month = date('Y-m');
    $category_id = $_POST['category_id'] ?? null;
    $amount = $_POST['amount'] ?? '0';

    // Clean formatting
    $amount = str_replace(['.', ','], '', $amount);

    if (!$category_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit();
    }

    try {
        // Check if budget exists
        $stmt = $pdo->prepare("SELECT id FROM budgets WHERE user_id = ? AND category_id = ? AND month = ?");
        $stmt->execute([$user_id, $category_id, $month]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE budgets SET amount = ? WHERE id = ?");
            $stmt->execute([$amount, $exists]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, month) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $amount, $month]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>