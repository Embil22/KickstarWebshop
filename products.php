<?php
session_start();
require_once 'database.php';

// Szűrési paraméterek
$category = isset($_GET['category']) ? $_GET['category'] : '';
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 100000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Termékek lekérése szűréssel
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($minPrice > 0) {
    $query .= " AND price >= :min_price";
    $params[':min_price'] = $minPrice;
}

if ($maxPrice < 100000) {
    $query .= " AND price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

// Rendezés
switch($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY name DESC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategóriák lekérése (példa - ha lenne kategória tábla)
$categories = ['Férfi', 'Női', 'Gyerek', 'Sport', 'Casual'];
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termékek - Kickstar Sneaker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Termék oldal specifikus stílusok */
        .products-page {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header h1 {
            color: var(--dark-color);
            position: relative;
            padding-left: 1rem;
        }
        
        .page-header h1::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        /* Filter szekció */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .price-range {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .price-range input {
            width: 100%;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            margin-top: 1rem;
        }
        
        .apply-filters {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .apply-filters:hover {
            background-color: #ff5252;
        }
        
        .reset-filters {
            background-color: var(--light-color);
            color: var(--dark-color);
            border: 1px solid #ddd;
            padding: 0.7rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }
        
        .reset-filters:hover {
            background-color: #e0e0e0;
        }
        
        /* Kereső mező */
        .search-box {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .search-box input {
            flex: 1;
            padding: 0.7rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        
        .search-box button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.7rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .search-box button:hover {
            background-color: #ff5252;
        }
        
        /* Termékek száma */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            color: #666;
        }
        
        /* Termék grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        /* Termék kártya bővítések */
        .product-card {
            position: relative;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: var(--primary-color);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }
        
        .product-badge.sale {
            background-color: var(--danger-color);
        }
        
        .product-badge.new {
            background-color: var(--success-color);
        }
        
        .product-image {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .product-overlay {
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1rem;
            transition: bottom 0.3s ease;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .product-card:hover .product-overlay {
            bottom: 0;
        }
        
        .quick-view {
            background-color: white;
            color: var(--dark-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .quick-view:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .product-info .price {
            font-size: 1.3rem;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .product-info .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
        
        .product-rating {
            color: #ffc107;
            margin-bottom: 0.5rem;
        }
        
        .product-rating span {
            color: #666;
            margin-left: 0.3rem;
            font-size: 0.9rem;
        }
        
        .add-to-cart {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: bold;
            transition: var(--transition);
            margin-top: 0.5rem;
        }
        
        .add-to-cart:hover {
            background-color: #ff5252;
        }
        
        .add-to-cart:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        /* Pagináció */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--dark-color);
            transition: var(--transition);
        }
        
        .pagination a:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Nincs találat */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            grid-column: 1 / -1;
        }
        
        .no-results i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .no-results h2 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .no-results p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .products-page {
                padding: 0 1rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .price-range {
                flex-direction: column;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .product-image {
                height: 220px;
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
                <li><a href="products.php" class="active">Termékek</a></li>
                <li><a href="cart.php">Kosár <span id="cart-count">0</span></a></li>
            </ul>
        </nav>
    </header>

    <main class="products-page">
        <div class="page-header">
            <h1>Összes termék</h1>
            <div class="results-info">
                <span><?php echo count($products); ?> termék található</span>
            </div>
        </div>

        <!-- Kereső mező -->
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Keresés termékek között..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Keresés</button>
        </form>

        <!-- Filter szekció -->
        <div class="filters-section">
            <form action="" method="GET" id="filter-form">
                <?php if(!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Kategória</label>
                        <select name="category">
                            <option value="">Összes kategória</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Rendezés</label>
                        <select name="sort">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Legújabb</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Ár szerint növekvő</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Ár szerint csökkenő</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Név szerint A-Z</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Név szerint Z-A</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Ár tartomány</label>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice ?: ''; ?>" min="0" step="1000">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice < 100000 ? $maxPrice : ''; ?>" min="0" step="1000">
                        </div>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <a href="products.php" class="reset-filters">Szűrők törlése</a>
                    <button type="submit" class="apply-filters">Szűrés</button>
                </div>
            </form>
        </div>

        <!-- Termékek grid -->
        <div class="products-grid">
            <?php if(empty($products)): ?>
                <div class="no-results">
                    <i>🔍</i>
                    <h2>Nincs találat</h2>
                    <p>Próbáld meg más keresési feltételekkel</p>
                    <a href="products.php" class="continue-shopping">Összes termék mutatása</a>
                </div>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <div class="product-card" data-id="<?php echo $product['id']; ?>">
                        
                        <div class="product-image">
                            <img src="uploads/<?php echo $product['image'] ?: 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            
                            <div class="price">
                                <?php echo number_format($product['price'], 0, ',', ' '); ?> Ft
                                <?php if($product['id'] % 3 == 0): ?>
                                    <span class="original-price">
                                        <?php echo number_format($product['price'] * 1.2, 0, ',', ' '); ?> Ft
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="product-description">
                                <?php echo mb_substr(htmlspecialchars($product['description']), 0, 100) . '...'; ?>
                            </p>
                            
                            <button class="add-to-cart" data-id="<?php echo $product['id']; ?>">
                                Kosárba tesz
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagináció (példa) -->
        <?php if(count($products) > 12): ?>
            <div class="pagination">
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">4</a>
                <a href="#">5</a>
                <span>...</span>
                <a href="#">10</a>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Kickstar - Minden jog fenntartva</p>
    </footer>

    <!-- Gyorsnézet modal -->
    <div id="quickViewModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="quickViewContent"></div>
        </div>
    </div>

    <script>
        // Kosár kezelés
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Kosár számláló frissítése
        function updateCartCount() {
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Termék hozzáadása a kosárhoz
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productCard = this.closest('.product-card');
                const productName = productCard.querySelector('h3').textContent;
                const productPrice = parseFloat(productCard.querySelector('.price').textContent.replace(/[^0-9]/g, ''));
                const productImage = productCard.querySelector('img').src.split('/').pop();
                
                const existingItem = cart.find(item => item.id === productId);
                
                if(existingItem) {
                    existingItem.quantity += 1;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        quantity: 1
                    });
                }
                
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartCount();
                
                // Animáció
                this.textContent = '✓ Hozzáadva!';
                this.style.backgroundColor = '#4CAF50';
                
                setTimeout(() => {
                    this.textContent = 'Kosárba tesz';
                    this.style.backgroundColor = '';
                }, 1500);
                
                // Értesítés
                showNotification(`${productName} kosárba téve!`);
            });
        });
        // Modal bezárás
        document.querySelector('.close')?.addEventListener('click', function() {
            document.getElementById('quickViewModal').style.display = 'none';
        });

        window.onclick = function(event) {
            const modal = document.getElementById('quickViewModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Értesítés megjelenítése
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #4CAF50;
                color: white;
                padding: 1rem 2rem;
                border-radius: 5px;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Oldal betöltésekor kosár számláló frissítése
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Animációk
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            
            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 2rem;
                border-radius: 8px;
                width: 90%;
                max-width: 800px;
                animation: modalSlideIn 0.3s ease;
            }
            
            @keyframes modalSlideIn {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            
            .close:hover {
                color: #333;
            }
            
            .active {
                color: var(--primary-color);
                font-weight: bold;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>