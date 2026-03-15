<?php
require_once 'database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // CORS engedélyezés

// Hibanaplózás bekapcsolása
error_reporting(E_ALL);
ini_set('display_errors', 1);

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if($product_id === 0) {
    echo json_encode(['error' => 'Hiányzó termék azonosító']);
    exit;
}

try {
    // Adatbázis kapcsolat ellenőrzése
    if (!isset($pdo)) {
        throw new Exception('Nincs adatbázis kapcsolat');
    }
    
    // Méretek lekérése a termékhez
    $stmt = $pdo->prepare("
        SELECT id, size 
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
    
    // Ha nincs méret, próbáljuk lekérni a terméket
    if(empty($sizes)) {
        $productCheck = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $productCheck->execute([$product_id]);
        $product = $productCheck->fetch();
        
        if(!$product) {
            echo json_encode(['error' => 'A termék nem található']);
            exit;
        }
    }
    
    echo json_encode([
        'success' => true,
        'sizes' => $sizes,
        'count' => count($sizes),
        'product_id' => $product_id
    ]);
    
} catch(Exception $e) {
    error_log("Hiba a get-product-sizes.php-ban: " . $e->getMessage());
    echo json_encode([
        'error' => 'Adatbázis hiba: ' . $e->getMessage()
    ]);
}
?>