<?php
session_start();
require_once 'config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = $data['code'] ?? '';

$response = ['success' => false, 'message' => ''];

if($code) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date > NOW())");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($coupon) {
        // Kosár összegének lekérése
        $subtotal = 0;
        foreach($_SESSION['cart'] ?? [] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Minimum rendelési összeg ellenőrzés
        if($coupon['min_order_amount'] && $subtotal < $coupon['min_order_amount']) {
            $response['message'] = 'A minimális rendelési összeg ' . number_format($coupon['min_order_amount'], 0, ',', ' ') . ' Ft';
        } else {
            // Kedvezmény számítás
            if($coupon['type'] == 'percent') {
                $discount = $subtotal * ($coupon['value'] / 100);
                if($coupon['max_discount']) {
                    $discount = min($discount, $coupon['max_discount']);
                }
            } else {
                $discount = $coupon['value'];
            }
            
            $_SESSION['coupon'] = [
                'code' => $code,
                'discount' => $discount,
                'type' => $coupon['type'],
                'value' => $coupon['value']
            ];
            
            $response['success'] = true;
            $response['message'] = 'Kupon sikeresen alkalmazva!';
        }
    } else {
        $response['message'] = 'Érvénytelen vagy lejárt kuponkód!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>