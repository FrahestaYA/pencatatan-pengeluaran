<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'] ?? null;
    $amount = isset($_POST['amount']) ? str_replace(['.', ','], '', $_POST['amount']) : null;
    $date = $_POST['date'] ?? null;
    $description = $_POST['description'] ?? '';

    // Basic Validation
    if (empty($amount) || empty($category_id) || empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, $amount, $date, $description]);

        // Return latest balance/stats if needed, or just success
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
?>