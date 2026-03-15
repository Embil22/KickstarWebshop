<?php
session_start();
require_once 'database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? 0;
    
    // Cím összeállítása
    $shipping_zip = $_POST['shipping_zip'] ?? '';
    $shipping_city = $_POST['shipping_city'] ?? '';
    $shipping_street = $_POST['shipping_street'] ?? '';
    $shipping_address = trim($shipping_zip . ', ' . $shipping_city . ', ' . $shipping_street, ', ');
    
    try {
        $stmt = $pdo->prepare("
            UPDATE orders SET 
                customer_name = ?,
                customer_email = ?,
                customer_phone = ?,
                shipping_address = ?,
                shipping_cost = ?,
                discount = ?,
                coupon_code = ?,
                status = ?,
                payment_status = ?,
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'] ?? null,
            $shipping_address,
            $_POST['shipping_cost'] ?? 0,
            $_POST['discount'] ?? 0,
            $_POST['coupon_code'] ?? null,
            $_POST['status'],
            $_POST['payment_status'] ?? 'pending',
            $_POST['notes'] ?? null,
            $order_id
        ]);
        
        header('Location: edit-order.php?id=' . $order_id . '&success=1');
        
    } catch(Exception $e) {
        header('Location: edit-order.php?id=' . $order_id . '&error=1');
    }
} else {
    header('Location: dashboard.php');
}
?>