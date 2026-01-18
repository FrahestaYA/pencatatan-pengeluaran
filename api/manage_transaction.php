<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'add';

try {
    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id)
            throw new Exception("ID Transaksi diperlukan");

        // Verify ownership
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        if ($stmt->rowCount() === 0)
            throw new Exception("Transaksi tidak ditemukan");

        echo json_encode(['success' => true]);

    } elseif ($action === 'add' || $action === 'update') {
        $category_id = $_POST['category_id'] ?? null;
        $amount = isset($_POST['amount']) ? str_replace(['.', ','], '', $_POST['amount']) : null;
        $date = $_POST['date'] ?? null;
        $description = $_POST['description'] ?? '';
        $id = $_POST['id'] ?? null; // For update

        if (empty($amount) || empty($category_id) || empty($date)) {
            throw new Exception("Data tidak lengkap");
        }

        if ($action === 'update') {
            if (!$id)
                throw new Exception("ID Transaksi diperlukan untuk edit");

            // Update
            $stmt = $pdo->prepare("UPDATE expenses SET category_id = ?, amount = ?, date = ?, description = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$category_id, $amount, $date, $description, $id, $user_id]);

            if ($stmt->rowCount() === 0) {
                // Check if it exists but no change
                $check = $pdo->prepare("SELECT id FROM expenses WHERE id = ? AND user_id = ?");
                $check->execute([$id, $user_id]);
                if (!$check->fetch())
                    throw new Exception("Transaksi tidak ditemukan");
            }

        } else {
            // Add
            $stmt = $pdo->prepare("INSERT INTO expenses (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $category_id, $amount, $date, $description]);
        }

        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Aksi tidak valid");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>