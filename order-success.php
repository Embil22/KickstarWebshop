<?php
session_start();

// Ellenőrizzük, hogy van-e sikeres rendelés
if(!isset($_SESSION['order_success']) || !$_SESSION['order_success']) {
    header('Location: index.php');
    exit;
}

$order_number = $_SESSION['order_number'];
$order_id = $_SESSION['order_id'];

// Töröljük a session adatokat
unset($_SESSION['order_success']);
unset($_SESSION['order_number']);
unset($_SESSION['order_id']);
unset($_SESSION['checkout_cart']);
unset($_SESSION['checkout_coupon']);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sikeres rendelés - Kickstar Sneaker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 3rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-container h1 {
            color: var(--dark-color);
            margin-bottom: 1rem;
        }

        .order-number {
            background: var(--light-color);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin: 2rem 0;
            font-size: 1.2rem;
        }

        .order-number strong {
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .success-message {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--light-color);
            color: var(--dark-color);
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 2rem;
                padding: 2rem;
            }

            .action-buttons {
                flex-direction: column;
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
                <li><a href="cart.php">Kosár <span id="cart-count">0</span></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="success-container">
            <div class="success-icon">✓</div>
            
            <h1>Köszönjük a rendelést!</h1>
            
            <p class="success-message">
                Sikeresen leadtad a rendelésed. Hamarosan visszaigazoló emailt küldünk a megadott címre.
            </p>
            
            <div class="order-number">
                Rendelésszámod: <strong><?php echo $order_number; ?></strong>
            </div>
            
            <p class="success-message">
                A rendelés állapotát bármikor nyomon követheted a profilodban,<br>
                vagy kapcsolatba léphetsz velünk a rendelésszámod megadásával.
            </p>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">Vissza a főoldalra</a>
                <a href="products.php" class="btn btn-secondary">Tovább vásárlás</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Kickstar Sneaker - Minden jog fenntartva</p>
    </footer>

    <script>
        // LocalStorage kosár ürítése
        localStorage.removeItem('cart');
        localStorage.removeItem('appliedCoupon');
        
        // Kosár számláló frissítése
        document.getElementById('cart-count').textContent = '0';
    </script>
</body>
</html>