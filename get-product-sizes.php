<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? 0;

if(!$product_id) {
    echo json_encode(['error' => 'Hiányzó termék azonosító']);
    exit;
}

try {
    // Méretek lekérése a termékhez
    $stmt = $pdo->prepare("
        SELECT id, size, stock 
        FROM product_variants 
        WHERE product_id = ? 
        ORDER BY 
            CASE 
                WHEN size REGEXP '^[0-9]+$' THEN CAST(size AS UNSIGNED) 
                ELSE 999 
            END
    ");
    $stmt->execute([$product_id]);
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sizes' => $sizes
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'error' => 'Adatbázis hiba: ' . $e->getMessage()
    ]);
}
?>