<?php
session_start();
require_once 'database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit;
}

$orderId = $_GET['order_id'] ?? $_GET['id'] ?? 0;

// Rendelés adatok
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    echo '<p style="color: red;">Rendelés nem található!</p>';
    exit;
}

// Szállítási cím feldarabolása (ha összetett)
$shipping_address = $order['shipping_address'] ?? '';
$address_parts = explode(',', $shipping_address);
$zip = trim($address_parts[0] ?? '');
$city = trim($address_parts[1] ?? '');
$street = trim($address_parts[2] ?? $shipping_address);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h2 {
            color: #333;
            margin-top: 0;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .status-select {
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-pending { background: #fde8e8; color: #e74c3c; }
        .status-processing { background: #e8f0fe; color: #3498db; }
        .status-shipped { background: #e8f8f5; color: #27ae60; }
        .status-delivered { background: #f0f0f0; color: #7f8c8d; }
        
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        .error-message {
            background: #fde8e8;
            color: #e74c3c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .readonly-field {
            background: #f8f9fa;
            cursor: not-allowed;
        }
        .info-box {
            background: #e7f3ff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #004085;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2>✏️ Rendelés szerkesztése #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>

        <?php if(isset($_GET['success'])): ?>
            <div class="success-message">✅ Rendelés sikeresen módosítva!</div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">❌ Hiba történt a módosítás során!</div>
        <?php endif; ?>

        <div class="info-box">
            <strong>📅 Rendelés leadva:</strong> <?php echo date('Y. F j. H:i', strtotime($order['created_at'])); ?>
        </div>

        <form method="POST" action="save-order-changes.php" id="editOrderForm">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

            <!-- Vásárló adatai -->
            <h3 style="color: #333; margin-bottom: 15px;">👤 Vásárló adatai</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Vásárló neve *</label>
                    <input type="text" name="customer_name" value="<?php echo htmlspecialchars($order['customer_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email cím *</label>
                    <input type="email" name="customer_email" value="<?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Telefonszám</label>
                <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($order['customer_phone'] ?? ''); ?>">
            </div>

            <!-- Szállítási cím - JAVÍTVA: shipping_address használata -->
            <h3 style="color: #333; margin: 25px 0 15px;">📦 Szállítási cím</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Irányítószám</label>
                    <input type="text" name="shipping_zip" value="<?php echo htmlspecialchars($zip); ?>">
                </div>
                
                <div class="form-group">
                    <label>Város</label>
                    <input type="text" name="shipping_city" value="<?php echo htmlspecialchars($city); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Utca, házszám</label>
                <input type="text" name="shipping_street" value="<?php echo htmlspecialchars($street); ?>">
            </div>

            <!-- Rendelés adatok -->
            <h3 style="color: #333; margin: 25px 0 15px;">💰 Rendelés adatai</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Részösszeg (Ft)</label>
                    <input type="number" name="subtotal" value="<?php echo $order['subtotal'] ?? 0; ?>" step="1" class="readonly-field" readonly>
                </div>
                
                <div class="form-group">
                    <label>Szállítási költség (Ft)</label>
                    <input type="number" name="shipping_cost" value="<?php echo $order['shipping_cost'] ?? 0; ?>" step="1">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kedvezmény (Ft)</label>
                    <input type="number" name="discount" value="<?php echo $order['discount'] ?? 0; ?>" step="1">
                </div>
                
                <div class="form-group">
                    <label>Kuponkód</label>
                    <input type="text" name="coupon_code" value="<?php echo htmlspecialchars($order['coupon_code'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Végösszeg (Ft)</label>
                <input type="number" name="total_amount" value="<?php echo $order['total_amount'] ?? 0; ?>" step="1" class="readonly-field" readonly>
            </div>

            <!-- Státuszok -->
            <h3 style="color: #333; margin: 25px 0 15px;">📊 Státuszok</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Rendelés státusz</label>
                    <select name="status" class="status-select status-<?php echo $order['status']; ?>">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Függőben</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>⚙️ Feldolgozás alatt</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🚚 Szállítás alatt</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>✅ Kiszállítva</option>
                    </select>
                </div>
                
            </div>

            <!-- Megjegyzés -->
            <div class="form-group">
                <label>📝 Megjegyzés a rendeléshez</label>
                <textarea name="notes" rows="4"><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
            </div>

            <!-- Gombok -->
            <div class="button-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">↩️ Vissza</button>
                <button type="button" class="btn btn-danger" onclick="deleteOrder(<?php echo $order['id']; ?>)">🗑️ Törlés</button>
                <button type="submit" class="btn btn-primary">💾 Módosítások mentése</button>
            </div>
        </form>
    </div>

    <script>
        // Form ellenőrzés
        document.getElementById('editOrderForm').addEventListener('submit', function(e) {
            const subtotal = parseFloat(this.subtotal.value) || 0;
            const shipping = parseFloat(this.shipping_cost.value) || 0;
            const discount = parseFloat(this.discount.value) || 0;
            const total = parseFloat(this.total_amount.value) || 0;
            
            // Ellenőrizzük, hogy a végösszeg egyezik-e
            const calculatedTotal = subtotal + shipping - discount;
            
            if(Math.abs(calculatedTotal - total) > 1) {
                if(!confirm('A végösszeg nem egyezik a részösszeg + szállítás - kedvezmény értékkel. Biztosan mented?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Mentés gomb letiltása
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Mentés...';
        });

        // Végösszeg automatikus számítás (opcionális)
        document.querySelectorAll('input[name="subtotal"], input[name="shipping_cost"], input[name="discount"]').forEach(input => {
            input.addEventListener('input', function() {
                const subtotal = parseFloat(document.querySelector('input[name="subtotal"]').value) || 0;
                const shipping = parseFloat(document.querySelector('input[name="shipping_cost"]').value) || 0;
                const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
                
                const total = subtotal + shipping - discount;
                document.querySelector('input[name="total_amount"]').value = total;
            });
        });

        // Rendelés törlése
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

        // Státusz változtatáskor vizuális visszajelzés
        document.querySelector('select[name="status"]').addEventListener('change', function() {
            this.className = 'status-select status-' + this.value;
        });
    </script>
</body>
</html>