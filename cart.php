<?php
session_start();
require_once 'database.php';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár - Kickstar Sneaker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Kosár oldal specifikus stílusok */
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .cart-header h1 {
            color: var(--dark-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cart-header p {
            color: #666;
        }

        /* Kosár tartalom */
        .cart-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }

        /* Kosár tételek */
        .cart-items {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: auto 1fr auto auto auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
            position: relative;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: var(--border-radius);
            overflow: hidden;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .cart-item-image img[src*="default.jpg"] {
            object-fit: contain;
            padding: 10px;
            opacity: 0.7;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }

        /* Méret megjelenítés */
        .cart-item-size {
            margin: 8px 0;
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .size-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .current-size {
            background: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .change-size-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
            padding: 4px 8px;
        }

        .change-size-btn:hover {
            color: #ff5252;
        }

        /* Méretválasztó */
        .size-selector-small {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .size-option {
            width: 50px;
            height: 50px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .size-option:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .size-option.selected {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Mennyiség kezelés */
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover:not(:disabled) {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Részösszeg */
        .cart-item-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--dark-color);
            min-width: 120px;
            text-align: right;
        }

        /* Eltávolítás gomb */
        .remove-item {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            transform: scale(1.1);
            color: #c0392b;
        }

        /* Kosár összegzés */
        .cart-summary {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            position: sticky;
            top: 100px;
        }

        .cart-summary h2 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
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

        /* Kupon rész */
        .coupon-section {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: var(--border-radius);
        }

        .coupon-input-group {
            display: flex;
            gap: 0.5rem;
        }

        .coupon-input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .apply-coupon {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .apply-coupon:hover {
            background: #45b7aa;
        }

        .coupon-applied {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .remove-coupon {
            color: var(--danger-color);
            cursor: pointer;
            font-weight: bold;
        }

        /* Checkout gomb */
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .checkout-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Kosár műveletek */
        .cart-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .clear-cart {
            flex: 1;
            padding: 0.8rem;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clear-cart:hover {
            background: #c0392b;
        }

        .continue-shopping {
            flex: 1;
            padding: 0.8rem;
            background: var(--light-color);
            color: var(--dark-color);
            text-align: center;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .continue-shopping:hover {
            background: #e0e0e0;
        }

        /* Üres kosár */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .empty-cart h2 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .empty-cart .checkout-btn {
            display: inline-block;
            width: auto;
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .empty-cart .checkout-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        /* Értesítések */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            color: white;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        .notification.success {
            background: var(--success-color);
        }

        .notification.error {
            background: var(--danger-color);
        }

        .notification.warning {
            background: #ff9800;
        }

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

        /* Loading spinner */
        .loading-sizes {
            text-align: center;
            padding: 15px;
            color: #666;
            width: 100%;
        }

        .loading-sizes::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Reszponzív */
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .cart-item-image {
                margin: 0 auto;
            }

            .cart-item-size {
                justify-content: center;
            }

            .size-selector-small {
                justify-content: center;
            }

            .cart-item-quantity {
                justify-content: center;
            }

            .cart-item-total {
                text-align: center;
            }

            .cart-actions {
                flex-direction: column;
            }
        }
        @media (max-width: 768px) {
            .cart-container {
                min-height: calc(100vh - 150px);
                padding: 1rem;
            }
            
            .empty-cart {
                padding: 2rem 1.5rem;
                max-width: 90%;
            }
            
            .empty-cart h2 {
                font-size: 1.5rem;
            }
            
            .empty-cart p {
                font-size: 0.9rem;
            }
            
            .empty-cart .checkout-btn {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .empty-cart {
                padding: 1.5rem 1rem;
            }
            
            .empty-cart i {
                font-size: 3.5rem;
            }
            
            .empty-cart h2 {
                font-size: 1.2rem;
            }
        }

        /* Hibakezelés */
        .error-message {
            color: var(--danger-color);
            padding: 10px;
            text-align: center;
            background: #ffe6e6;
            border-radius: 4px;
            margin: 10px 0;
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
                <li><a href="cart.php" class="active">Kosár <span id="cart-count">0</span></a></li>
            </ul>
        </nav>
    </header>

    <main class="cart-container">
        <div class="cart-header">
            <h1>Kosaram</h1>
            <p>A kiválasztott sneakerek, egy helyen</p>
        </div>

        <div id="cart-content" class="cart-content">
            <!-- Ide töltődik be JavaScript-el a kosár tartalma -->
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Kickstar Sneaker - Minden jog fenntartva</p>
    </footer>

    <script>
    // Kosár betöltése localStorage-ból
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let appliedCoupon = null;
    
    // Kuponok
    const coupons = {
        'KICK10': { type: 'percent', value: 10, min_order: 10000 },
        'KICK20': { type: 'percent', value: 20, min_order: 20000 },
        'FREE1000': { type: 'fixed', value: 1000, min_order: 5000 }
    };

    // Termék képek hozzárendelése ID alapján (ha hiányzik a kép)
    const productImages = {
        1: 'nike-air-max-270.jpg',
        2: 'adidas-ultraboost-22.jpg',
        3: 'new-balance-574.jpg',
        4: 'puma-cali.jpg',
        5: 'converse-chuck-taylor.jpg',
        6: 'vans-old-skool.jpg',
        7: 'AJ1MDS.jpg',
    };

    // Oldal betöltésekor
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== KOSÁR TARTALMA ===');
        console.log(JSON.parse(JSON.stringify(cart)));
        
        // HIBAJAVÍTÁS: Ha nincs kép a terméknél, hozzárendeljük az ID alapján
        let modified = false;
        cart = cart.map(item => {
            if (!item.image && productImages[item.id]) {
                console.log(`Kép hozzáadva a ${item.name} termékhez: ${productImages[item.id]}`);
                item.image = productImages[item.id];
                modified = true;
            }
            return item;
        });
        
        if (modified) {
            localStorage.setItem('cart', JSON.stringify(cart));
            console.log('Javított kosár:', JSON.parse(JSON.stringify(cart)));
        }
        
        displayCart();
        updateCartCount();
        
        const savedCoupon = localStorage.getItem('appliedCoupon');
        if(savedCoupon) {
            appliedCoupon = JSON.parse(savedCoupon);
        }
    });

    // Kosár megjelenítése
    function displayCart() {
        const cartContent = document.getElementById('cart-content');
        
        if (cart.length === 0) {
            cartContent.innerHTML = `
                <div class="empty-cart">
                    <i>🛒</i>
                    <h2>A kosár üres</h2>
                    <p>Nézz körül a termékek között, és válaszd ki a kedvenc sneaker-eidet!</p>
                    <a href="products.php" class="checkout-btn" style="display: inline-block; width: auto; padding: 1rem 3rem;">Termékek böngészése</a>
                </div>
            `;
            return;
        }
        
        let subtotal = 0;
        let itemsHtml = '';
        
        for(let i = 0; i < cart.length; i++) {
            const item = cart[i];
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            // Kép elérési út
            const imageFile = item.image || productImages[item.id] || 'default.jpg';
            const imagePath = `uploads/${imageFile}`;
            
            console.log(`Termék ${i}: ${item.name}, Kép: ${imagePath}`);
            
            itemsHtml += `
                <div class="cart-item" data-index="${i}" data-product-id="${item.id}">
                    <div class="cart-item-image">
                        <img src="${imagePath}" 
                             alt="${item.name}"
                             onerror="this.onerror=null; this.src='uploads/default.jpg'; console.log('Kép nem található: ${imagePath}');">
                    </div>
                    
                    <div class="cart-item-details">
                        <h3>${item.name}</h3>
                        <div class="cart-item-price">${item.price.toLocaleString()} Ft / db</div>
                        
                        <!-- Méret megjelenítés -->
                        <div class="cart-item-size">
                            <span class="size-label">Méret:</span>
                            <span class="current-size">${item.size || 'Nincs kiválasztva'}</span>
                            <button class="change-size-btn" onclick="showSizeSelector(${i})">Módosítás</button>
                        </div>
                        
                        <!-- Méretválasztó -->
                        <div id="size-selector-${i}" class="size-selector-small" style="display: none;">
                            <div class="loading-sizes">Méretek betöltése...</div>
                        </div>
                    </div>
                    
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="updateQuantity(${i}, -1)" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${i}, 1)">+</button>
                    </div>
                    
                    <div class="cart-item-total">${itemTotal.toLocaleString()} Ft</div>
                    
                    <button class="remove-item" onclick="removeItem(${i})">🗑️</button>
                </div>
            `;
        }
        
        const shipping = subtotal >= 30000 ? 0 : 1990;
        const total = subtotal + shipping;
        
        cartContent.innerHTML = `
            <div class="cart-items">
                ${itemsHtml}
            </div>
            <div class="cart-summary">
                <h2>Összegzés</h2>
                
                <div class="summary-row">
                    <span>Részösszeg:</span>
                    <span>${subtotal.toLocaleString()} Ft</span>
                </div>
                
                <div class="summary-row">
                    <span>Szállítás:</span>
                    <span>${shipping === 0 ? 'Ingyenes' : shipping.toLocaleString() + ' Ft'}</span>
                </div>
                
                <div class="summary-row total grand-total">
                    <span>Végösszeg:</span>
                    <span>${total.toLocaleString()} Ft</span>
                </div>
                
                <button class="checkout-btn" onclick="proceedToCheckout()">
                    Tovább a pénztárhoz
                </button>
                
                <div class="cart-actions">
                    <button class="clear-cart" onclick="clearCart()">Kosár ürítése</button>
                    <a href="products.php" class="continue-shopping">Vásárlás folytatása</a>
                </div>
            </div>
        `;
        
        // Méretek betöltése minden termékhez
        for(let i = 0; i < cart.length; i++) {
            if (cart[i].id) {
                loadAvailableSizes(i, cart[i].id, cart[i].size);
            }
        }
    }

    // Elérhető méretek betöltése
    function loadAvailableSizes(index, productId, currentSize) {
        fetch(`get-product-sizes.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                const selector = document.getElementById(`size-selector-${index}`);
                if (!selector) return;
                
                if (data.sizes && data.sizes.length > 0) {
                    let html = '';
                    data.sizes.forEach(size => {
                        const selectedClass = size.size === currentSize ? 'selected' : '';
                        html += `
                            <button class="size-option ${selectedClass}" 
                                    onclick="changeSize(${index}, '${size.size}', ${size.id})">
                                ${size.size}
                            </button>
                        `;
                    });
                    selector.innerHTML = html;
                } else {
                    selector.innerHTML = '<p style="color: #999;">Nincsenek elérhető méretek</p>';
                }
            })
            .catch(error => {
                console.error('Hiba:', error);
            });
    }

    // Méretválasztó megjelenítése
    function showSizeSelector(index) {
        const selector = document.getElementById(`size-selector-${index}`);
        if (selector) {
            selector.style.display = selector.style.display === 'none' ? 'flex' : 'none';
        }
    }

    // Méret módosítása
    function changeSize(index, newSize, variantId) {
        cart[index].size = newSize;
        cart[index].variantId = variantId;
        localStorage.setItem('cart', JSON.stringify(cart));
        displayCart();
        showNotification(`Méret módosítva: ${newSize}`);
    }

    // Mennyiség frissítése
    function updateQuantity(index, change) {
        const newQuantity = cart[index].quantity + change;
        if (newQuantity >= 1 && newQuantity <= 10) {
            cart[index].quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
            updateCartCount();
        }
    }

    // Termék eltávolítása
    function removeItem(index) {
        if (confirm('Biztosan eltávolítod?')) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
            updateCartCount();
            showNotification('Termék eltávolítva');
        }
    }

    // Kosár ürítése
    function clearCart() {
        if (confirm('Biztosan üríteni szeretnéd a kosarat?')) {
            cart = [];
            localStorage.removeItem('cart');
            displayCart();
            updateCartCount();
            showNotification('Kosár kiürítve');
        }
    }

    // Kosár számláló frissítése
    function updateCartCount() {
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        document.getElementById('cart-count').textContent = count;
    }

    // Tovább a pénztárhoz
    function proceedToCheckout() {
        if (cart.length === 0) {
            showNotification('A kosár üres!', 'error');
            return;
        }
        
        // Ellenőrizzük a méreteket
        for (let item of cart) {
            if (!item.size) {
                showNotification(`A(z) ${item.name} termékhez nincs méret kiválasztva!`, 'error');
                return;
            }
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'checkout.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cart_data';
        input.value = JSON.stringify(cart);
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // Értesítés
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
</script>
</body>
</html>
