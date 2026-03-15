<?php
session_start();
require_once 'database.php';

// Header beállítása a JSON válaszhoz
header('Content-Type: application/json');

// Admin jogosultság ellenőrzés
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nincs jogosultság']);
    exit;
}

// POST adatok fogadása
$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['order_id'] ?? 0;

if(!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó rendelés azonosító']);
    exit;
}

try {
    // Tranzakció indítása
    $pdo->beginTransaction();
    
    // 1. Rendelés tételek törlése
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $itemsDeleted = $stmt->rowCount();
    
    // 2. Kupon használat törlése (ha van)
    $stmt = $pdo->prepare("DELETE FROM coupon_usage WHERE order_id = ?");
    $stmt->execute([$orderId]);
    
    // 3. Rendelés törlése
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $orderDeleted = $stmt->rowCount();
    
    if($orderDeleted > 0) {
        // Sikeres törlés
        $pdo->commit();
        
        // Naplózás
        $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $logStmt->execute([
            $_SESSION['admin_id'],
            'delete_order',
            "Rendelés #$orderId törölve ($itemsDeleted tétellel)",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rendelés sikeresen törölve',
            'deleted_items' => $itemsDeleted
        ]);
    } else {
        throw new Exception('A rendelés nem található');
    }
    
} catch(Exception $e) {
    // Hiba esetén visszagörgetés
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false, 
        'message' => 'Hiba a törlés során: ' . $e->getMessage()
    ]);
}
?>