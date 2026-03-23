<?php
session_start();
require_once 'database.php';

// Termékek lekérése
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kickstar - Sneaker webshop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">Kickstar</div>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="products.php">Termékek</a></li>
                <li><a href="cart.php">Kosár <span id="cart-count">0</span></a></li>
                <li><a href="login.php">🖥️Admin felület</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Üdvözöl a Kickstar!</h1>
            <p>A legmenőbb sneakerek egy helyen</p>
        </section>

        <section class="featured-products">
            <h2>Kiemelt termékek</h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price"><?php echo number_format($product['price'], 0, ',', ' '); ?> Ft</div>
                        <div class="product-description">
                            <?php echo mb_substr(htmlspecialchars($product['description'] ?? 'Nincs leírás'), 0, 100); ?>...
                        </div>
                        <button class="add-to-cart" data-id="<?php echo $product['id']; ?>">Kosárba</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Kickstar - Minden jog fenntartva</p>
    </footer>
    <script src="script.js"></script>
</body>

</html>
