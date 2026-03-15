<?php
session_start();
require_once 'database.php';

// Kosár adatok feldolgozása
$cart = [];
$appliedCoupon = null;

// LocalStorage-ból érkező adatok
if(isset($_POST['cart_data'])) {
    $cart = json_decode($_POST['cart_data'], true);
    $_SESSION['checkout_cart'] = $cart;
    
    if(isset($_POST['coupon_data']) && $_POST['coupon_data'] !== 'null') {
        $appliedCoupon = json_decode($_POST['coupon_data'], true);
        $_SESSION['checkout_coupon'] = $appliedCoupon;
    }
} 
// Session-ből töltjük
else if(isset($_SESSION['checkout_cart'])) {
    $cart = $_SESSION['checkout_cart'];
    $appliedCoupon = $_SESSION['checkout_coupon'] ?? null;
}

// Ha még mindig nincs kosár, vissza a cart.php-ra
if(empty($cart)) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

// Felhasználó adatok (ha be van jelentkezve)
$user = null;
if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Kosár összegzés
$subtotal = 0;
foreach($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Szállítási költség
$shipping_cost = 1990;
$free_shipping_threshold = 30000;

if($subtotal >= $free_shipping_threshold) {
    $shipping_cost = 0;
}

// Kedvezmény számítás - JAVÍTVA!
$discount = 0;
if($appliedCoupon) {
    if($appliedCoupon['type'] === 'percent') {
        $discount = round($subtotal * ($appliedCoupon['value'] / 100));
    } else {
        $discount = $appliedCoupon['value'];
    }
}

$total = $subtotal + $shipping_cost - $discount;

// Rendelés feldolgozás
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $shipping_zip = $_POST['shipping_zip'] ?? '';
    $shipping_city = $_POST['shipping_city'] ?? '';
    $shipping_street = $_POST['shipping_street'] ?? '';
    $shipping_address = $shipping_zip . ', ' . $shipping_city . ', ' . $shipping_street;
    
    $payment_method = $_POST['payment_method'] ?? 'bank_transfer';
    $shipping_method = $_POST['shipping_method'] ?? 'home_delivery';
    $notes = $_POST['notes'] ?? '';
    
    // Validáció
    $errors = [];
    
    if(empty($customer_name)) $errors[] = 'A név megadása kötelező!';
    if(empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Érvényes email cím megadása kötelező!';
    if(empty($customer_phone)) $errors[] = 'A telefonszám megadása kötelező!';
    if(empty($shipping_zip)) $errors[] = 'Az irányítószám megadása kötelező!';
    if(empty($shipping_city)) $errors[] = 'A város megadása kötelező!';
    if(empty($shipping_street)) $errors[] = 'Az utca és házszám megadása kötelező!';
    if(!isset($_POST['terms'])) $errors[] = 'Az ÁSZF elfogadása kötelező!';
    
    if(empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Rendelési szám generálása
            $order_number = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Rendelés beszúrása
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_email, customer_phone,
                    shipping_address, payment_method, shipping_method, subtotal,
                    shipping_cost, discount, total_amount, coupon_code, notes,
                    status, payment_status, shipping_status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    'pending', 'pending', 'pending', NOW()
                )
            ");
            
            $stmt->execute([
                $order_number,
                $user['id'] ?? null,
                $customer_name,
                $customer_email,
                $customer_phone,
                $shipping_address,
                $payment_method,
                $shipping_method,
                $subtotal,
                $shipping_cost,
                $discount,
                $total,
                $appliedCoupon['code'] ?? null,
                $notes
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Rendelés tételek beszúrása
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, product_name, product_sku,
                    quantity, price, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach($cart as $item) {
                // Termék SKU lekérése
                $product_stmt = $pdo->prepare("SELECT sku FROM products WHERE id = ?");
                $product_stmt->execute([$item['id']]);
                $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
                
                $item_total = $item['price'] * $item['quantity'];
                
                $item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['name'],
                    $product['sku'] ?? '',
                    $item['quantity'],
                    $item['price'],
                    $item_total
                ]);
                
                // Készlet csökkentés
                $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stmt->execute([$item['quantity'], $item['id']]);
            }
            
            $pdo->commit();
            
            // Session és localStorage törlése
            unset($_SESSION['checkout_cart']);
            unset($_SESSION['checkout_coupon']);
            
            // Sikeres rendelés - átirányítás
            $_SESSION['order_success'] = true;
            $_SESSION['order_number'] = $order_number;
            $_SESSION['order_id'] = $order_id;
            
            header('Location: order-success.php');
            exit;
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = 'Hiba történt a rendelés feldolgozása során: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pénztár - Kickstar Sneaker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Checkout specifikus stílusok */
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .checkout-header h1 {
            color: var(--dark-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .checkout-header p {
            color: #666;
        }

        .checkout-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .step.active .step-number {
            background: var(--primary-color);
            color: white;
        }

        .step.completed .step-number {
            background: var(--success-color);
            color: white;
        }

        .step-line {
            width: 100px;
            height: 2px;
            background: #e0e0e0;
            margin: 0 1rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        /* Checkout form */
        .checkout-form {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .form-section h2 {
            color: var(--dark-color);
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h2 i {
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255,107,107,0.1);
        }

        /* Fizetési módok */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-method label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: var(--primary-color);
            background: #fff5f5;
        }

        .payment-method i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Order summary */
        .order-summary {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            position: sticky;
            top: 100px;
        }

        .order-summary h2 {
            color: var(--dark-color);
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .summary-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-item-name {
            font-weight: 500;
        }

        .summary-item-price {
            color: #666;
            font-size: 0.9rem;
        }

        .summary-item-total {
            font-weight: bold;
            color: var(--primary-color);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-color);
            padding-top: 1rem;
        }

        .summary-row.grand-total {
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Error box */
        .error-box {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }

        /* Login prompt */
        .login-prompt {
            background: #e7f3ff;
            color: #004085;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid #b8daff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-link {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
        }

        .login-link:hover {
            background: #ff5252;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .checkout-steps {
                flex-direction: column;
                gap: 1rem;
            }

            .step-line {
                width: 2px;
                height: 30px;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Kickstar</div>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="products.php">Termékek</a></li>
                <li><a href="cart.php">Kosár <span id="cart-count"><?php echo array_sum(array_column($cart, 'quantity')); ?></span></a></li>
            </ul>
        </nav>
    </header>

    <main class="checkout-container">
        <div class="checkout-header">
            <h1>Pénztár</h1>
            <p>Még egy lépés és a tiéd lehet a kiválasztott sneaker!</p>
        </div>

        <!-- Checkout steps -->
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-number">✓</div>
                <span>Kosár</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-number">2</div>
                <span>Pénztár</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Befejezés</span>
            </div>
        </div>

        <?php if($error): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="checkout-grid">
            <!-- Checkout form -->
            <form method="POST" action="" class="checkout-form" id="checkoutForm">
                <input type="hidden" name="place_order" value="1">
                
                <!-- Személyes adatok -->
                <div class="form-section">
                    <h2><i>👤</i> Személyes adatok</h2>
                    
                    <div class="form-group">
                        <label>Teljes név *</label>
                        <input type="text" name="customer_name" value="<?php echo ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email cím *</label>
                            <input type="email" name="customer_email" value="<?php echo $user['email'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Telefonszám *</label>
                            <input type="tel" name="customer_phone" value="<?php echo $user['phone'] ?? ''; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Szállítási cím -->
                <div class="form-section">
                    <h2><i>📦</i> Szállítási cím</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Irányítószám *</label>
                            <input type="text" name="shipping_zip" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Város *</label>
                            <input type="text" name="shipping_city" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Utca, házszám *</label>
                        <input type="text" name="shipping_street" required>
                    </div>
                </div>

                <!-- Szállítási mód -->
                <div class="form-section">
                    <h2><i>🚚</i> Szállítási mód</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" name="shipping_method" id="shipping_home" value="home_delivery" checked>
                            <label for="shipping_home">
                                <i>🏠</i>
                                <strong>Házhozszállítás</strong>
                                <span><?php echo $shipping_cost > 0 ? number_format($shipping_cost, 0, ',', ' ') . ' Ft' : 'Ingyenes'; ?></span>
                                <small>1-3 munkanap</small>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" name="shipping_method" id="shipping_pickup" value="pickup">
                            <label for="shipping_pickup">
                                <i>🏪</i>
                                <strong>Személyes átvétel</strong>
                                <span>Ingyenes</span>
                                <small>Budapest, Kossuth tér 1-3.</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Fizetési mód -->
                <div class="form-section">
                    <h2><i>💳</i> Fizetési mód</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="payment_bank" value="bank_transfer" checked>
                            <label for="payment_bank">
                                <i>🏦</i>
                                <strong>Banki átutalás</strong>
                                <small>Előre utalás</small>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="payment_card" value="card">
                            <label for="payment_card">
                                <i>💳</i>
                                <strong>Bankkártya</strong>
                                <small>Online fizetés</small>
                            </label>
                        </div>
                        
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="payment_cod" value="cod">
                            <label for="payment_cod">
                                <i>💵</i>
                                <strong>Utánvét</strong>
                                <small>+ 490 Ft</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Megjegyzés -->
                <div class="form-section">
                    <h2><i>📝</i> Megjegyzés a rendeléshez</h2>
                    
                    <div class="form-group">
                        <textarea name="notes" rows="3" placeholder="Ha van bármilyen különleges kérésed, itt jelezheted..."></textarea>
                    </div>
                </div>

                <!-- ÁSZF elfogadás -->
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="terms" required>
                        <span>Elfogadom az <a href="aszf.php" target="_blank">Általános Szerződési Feltételeket</a> *</span>
                    </label>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Rendelés véglegesítése (<?php echo number_format($total, 0, ',', ' '); ?> Ft)
                </button>
            </form>

            <!-- Order summary -->
            <div class="order-summary">
                <h2>Rendelés összegzése</h2>
                
                <div class="summary-items">
                    <?php foreach($cart as $item): ?>
                    <div class="summary-item">
                        <div>
                            <div class="summary-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="summary-item-price">
                                <?php echo number_format($item['price'], 0, ',', ' '); ?> Ft × <?php echo $item['quantity']; ?> db
                            </div>
                        </div>
                        <div class="summary-item-total">
                            <?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Ft
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Összegzés -->
                <div class="summary-row">
                    <span>Részösszeg:</span>
                    <span><?php echo number_format($subtotal, 0, ',', ' '); ?> Ft</span>
                </div>
                
                <div class="summary-row">
                    <span>Szállítási költség:</span>
                    <span><?php echo $shipping_cost > 0 ? number_format($shipping_cost, 0, ',', ' ') . ' Ft' : 'Ingyenes'; ?></span>
                </div>
                
                <?php if($discount > 0): ?>
                <div class="summary-row" style="color: var(--success-color);">
                    <span>Kedvezmény (<?php echo $appliedCoupon['code']; ?>):</span>
                    <span>-<?php echo number_format($discount, 0, ',', ' '); ?> Ft</span>
                </div>
                <?php endif; ?>
                
                <div class="summary-row total grand-total">
                    <span>Végösszeg:</span>
                    <span><?php echo number_format($total, 0, ',', ' '); ?> Ft</span>
                </div>

                <!-- Vissza a kosárhoz link -->
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="cart.php" style="color: #666; text-decoration: none;">← Vissza a kosárhoz</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Kickstar Sneaker - Minden jog fenntartva</p>
    </footer>

    <script>
        // Form ellenőrzés
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Feldolgozás...';
        });

        // Utánvétes fizetés esetén plusz költség
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.value === 'cod') {
                    // Itt lehetne frissíteni az összeget
                    showNotification('Utánvétes fizetés esetén +490 Ft kezelési költség', 'info');
                }
            });
        });

        // Értesítés megjelenítése
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 2rem;
                border-radius: var(--border-radius);
                color: white;
                background: ${type === 'info' ? '#17a2b8' : '#28a745'};
                z-index: 9999;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>