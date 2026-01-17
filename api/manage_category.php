<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'expense';
        $icon = $_POST['icon'] ?? 'circle';

        // Basic Validation
        if (empty($name))
            throw new Exception("Nama kategori wajib diisi");

        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $type, $icon]);

        echo json_encode(['success' => true]);

    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        // Ensure user owns this category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Gagal menghapus (Mungkin kategori bawaan)");
        }
    } else {
        throw new Exception("Aksi tidak valid");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>