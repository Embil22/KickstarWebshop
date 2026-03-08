<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Nincs jogosultság']);
    exit;
}

header('Content-Type: application/json');

try {
    // Statisztikák lekérése
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();
    $todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    
    echo json_encode([
        'totalOrders' => $totalOrders,
        'pendingOrders' => $pendingOrders,
        'todayOrders' => $todayOrders,
        'totalRevenue' => $totalRevenue ?: 0
    ]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>