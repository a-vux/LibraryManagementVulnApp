<?php
require_once 'init.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    // echo $user_id;
    // Không cho xóa chính mình hoặc user admin khác
    if ($_SESSION['user']['id'] == $user_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete yourself.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);