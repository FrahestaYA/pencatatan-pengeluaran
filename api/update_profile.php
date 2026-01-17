<?php
session_start();
require_once '../conf/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Inputs
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $bio = $_POST['bio'] ?? '';

    try {
        // Handle File Upload
        $avatar_sql = "";
        $params = [$full_name, $email, $bio];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['avatar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_name = uniqid('av_', true) . '.' . $ext;
                $dest = '../assets/uploads/avatars/' . $new_name;

                if (!is_dir('../assets/uploads/avatars/')) {
                    mkdir('../assets/uploads/avatars/', 0777, true);
                }

                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $avatar_sql = ", avatar = ?";
                    $params[] = $new_name;
                }
            }
        }

        // Update DB
        $params[] = $user_id;
        $sql = "UPDATE users SET full_name = ?, email = ?, bio = ? $avatar_sql WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update Session
        $_SESSION['full_name'] = $full_name;
        if (isset($new_name)) {
            $_SESSION['avatar'] = $new_name;
        }

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