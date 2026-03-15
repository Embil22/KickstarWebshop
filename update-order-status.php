<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nincs jogosultság']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['order_id'];
$status = $data['status'];

// Érvényes státuszok
$validStatuses = ['pending', 'processing', 'shipped', 'delivered'];

if(!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen státusz']);
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$success = $stmt->execute([$status, $orderId]);

if($success) {
    // Naplózás
    $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details) VALUES (?, ?, ?)");
    $logStmt->execute([
        $_SESSION['admin_id'],
        'status_update',
        "Rendelés #$orderId státusz módosítás: $status"
    ]);
}

echo json_encode(['success' => $success]);
?>