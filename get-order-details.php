<?php
session_start();
require_once 'database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit;
}

$orderId = $_GET['order_id'];

// Rendelés adatok
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    echo '<p style="color: red;">Rendelés nem található!</p>';
    exit;
}

// Rendelés tételek
$stmt = $pdo->prepare("
    SELECT oi.*, p.image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Szállítási cím feldarabolása
$addressParts = explode(',', $order['shipping_address']);
$city = trim($addressParts[0] ?? '');
$zip = trim($addressParts[1] ?? '');
$street = trim($addressParts[2] ?? '');
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <style>
        .order-details {
            font-family: Arial, sans-serif;
        }
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .order-header h2 {
            margin: 0;
            font-size: 24px;
        }
        .order-header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fde8e8; color: #e74c3c; }
        .status-processing { background: #e8f0fe; color: #3498db; }
        .status-shipped { background: #e8f8f5; color: #27ae60; }
        .status-delivered { background: #f0f0f0; color: #7f8c8d; }
        
        .address-box {
            background: white;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .address-box h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }
        .address-box p {
            margin: 5px 0;
            color: #666;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #666;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .items-table tfoot tr {
            background: #f8f9fa;
            font-weight: bold;
        }
        .items-table tfoot td {
            padding: 15px 12px;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        .product-info {
            display: flex;
            align-items: center;
        }
        .total-row {
            font-size: 18px;
            color: #667eea;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a67d8;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="order-details">
        <!-- Fejléc -->
        <div class="order-header">
            <h2>Rendelés részletei #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
            <p>Leadva: <?php echo date('Y. F j. H:i', strtotime($order['created_at'])); ?></p>
        </div>

        <!-- Rendelés információk -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Rendelés státusza</span>
                <span class="info-value">
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php 
                        switch($order['status']) {
                            case 'pending': echo 'Függőben'; break;
                            case 'processing': echo 'Feldolgozás alatt'; break;
                            case 'shipped': echo 'Szállítás alatt'; break;
                            case 'delivered': echo 'Kiszállítva'; break;
                            default: echo $order['status'];
                        }
                        ?>
                    </span>
                </span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Fizetés státusza</span>
                <span class="info-value"><?php echo $order['payment_status'] ?? 'pending'; ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Fizetés módja</span>
                <span class="info-value">
                    <?php 
                    switch($order['payment_method']) {
                        case 'bank_transfer': echo 'Banki átutalás'; break;
                        case 'card': echo 'Bankkártya'; break;
                        case 'cod': echo 'Utánvét'; break;
                        default: echo $order['payment_method'];
                    }
                    ?>
                </span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Szállítás módja</span>
                <span class="info-value">
                    <?php 
                    switch($order['shipping_method']) {
                        case 'home_delivery': echo 'Házhozszállítás'; break;
                        case 'pickup': echo 'Személyes átvétel'; break;
                        default: echo $order['shipping_method'];
                    }
                    ?>
                </span>
            </div>
        </div>

        <!-- Vásárló adatai -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="address-box">
                <h3>👤 Vásárló adatai</h3>
                <p><strong>Név:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($order['customer_phone'] ?? 'Nincs megadva'); ?></p>
            </div>

            <div class="address-box">
                <h3>📦 Szállítási cím</h3>
                <p><strong>Irányítószám:</strong> <?php echo htmlspecialchars($zip); ?></p>
                <p><strong>Város:</strong> <?php echo htmlspecialchars($city); ?></p>
                <p><strong>Cím:</strong> <?php echo htmlspecialchars($street); ?></p>
            </div>
        </div>

        <!-- Megjegyzés (ha van) -->
        <?php if(!empty($order['notes'])): ?>
        <div class="address-box">
            <h3>📝 Megjegyzés a rendeléshez</h3>
            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Rendelt termékek -->
        <h3 style="margin-bottom: 15px;">🛍️ Rendelt termékek</h3>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Termék</th>
                    <th>Mennyiség</th>
                    <th>Egységár</th>
                    <th>Részösszeg</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach($items as $item): 
                    $itemTotal = $item['quantity'] * $item['price'];
                    $subtotal += $itemTotal;
                ?>
                <tr>
                    <td>
                        <div class="product-info">
                            <?php if(!empty($item['image'])): ?>
                                <img src="uploads/<?php echo $item['image']; ?>" class="product-image" alt="<?php echo $item['product_name']; ?>">
                            <?php endif; ?>
                            <div>
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                <?php if(!empty($item['product_sku'])): ?>
                                    <br><small style="color: #666;">SKU: <?php echo $item['product_sku']; ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo $item['quantity']; ?> db</td>
                    <td><?php echo number_format($item['price'], 0, ',', ' '); ?> Ft</td>
                    <td><strong><?php echo number_format($itemTotal, 0, ',', ' '); ?> Ft</strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Részösszeg:</strong></td>
                    <td><strong><?php echo number_format($subtotal, 0, ',', ' '); ?> Ft</strong></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right;">Szállítási költség:</td>
                    <td><?php echo number_format($order['shipping_cost'] ?? 0, 0, ',', ' '); ?> Ft</td>
                </tr>
                <?php if($order['discount'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align: right; color: #27ae60;">Kedvezmény (<?php echo $order['coupon_code']; ?>):</td>
                    <td style="color: #27ae60;">-<?php echo number_format($order['discount'], 0, ',', ' '); ?> Ft</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3" style="text-align: right; font-size: 16px;"><strong>Végösszeg:</strong></td>
                    <td style="font-size: 18px; color: #667eea;"><strong><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Műveletek -->
        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">🖨️ Nyomtatás</button>
            <button class="btn btn-primary" onclick="editOrder(<?php echo $order['id']; ?>)">✏️ Szerkesztés</button>
            <button class="btn btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">🗑️ Törlés</button>
        </div>
    </div>

    <script>
        // Nyomtatás
        function printOrder() {
            window.print();
        }

        // Szerkesztés
        function editOrder(orderId) {
            // Modal vagy átirányítás a szerkesztő oldalra
            window.location.href = 'edit-order.php?id=' + orderId;
        }

        // Törlés
        function deleteOrder(orderId) {
            if(confirm('Biztosan törölni szeretnéd ezt a rendelést? Ez a művelet nem visszavonható!')) {
                fetch('delete-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({order_id: orderId})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Rendelés sikeresen törölve!');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Hiba történt a törlés során!');
                    }
                });
            }
        }
    </script>
</body>
</html>
