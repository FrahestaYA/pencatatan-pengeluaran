<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY type, name");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'expense';
        $icon = $_POST['icon'] ?? 'circle';

        // Basic Validation
        if (empty($name))
            throw new Exception("Nama kategori wajib diisi");

        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $type, $icon]);

        echo json_encode(['success' => true]);

    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'expense';
        $icon = $_POST['icon'] ?? 'circle';

        if (empty($name))
            throw new Exception("Nama kategori wajib diisi");

        // Usage Check
        $check = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Kategori yang sudah digunakan tidak bisa diedit");
        }

        $stmt = $pdo->prepare("UPDATE categories SET name = ?, type = ?, icon = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $type, $icon, $id, $user_id]);

        echo json_encode(['success' => true]);

    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        // Usage Check
        $check = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Kategori yang sudah digunakan tidak bisa dihapus. Silahkan hapus transaksi terkait terlebih dahulu.");
        }

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