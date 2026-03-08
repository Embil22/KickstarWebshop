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

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
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
            grid-template-columns: auto 1fr auto auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 0.3rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .cart-item-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--dark-color);
            min-width: 100px;
            text-align: right;
        }

        .remove-item {
            background-color: red;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            transform: scale(1.1);
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
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-cart h2 {
            color: var(--dark-color);
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 2rem;
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

            .cart-item-quantity {
                justify-content: center;
            }

            .cart-item-total {
                text-align: center;
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
        // Kosár kezelés
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let appliedCoupon = null;
        
        // Kuponok
        const coupons = {
            'KICK10': { type: 'percent', value: 10, min_order: 10000 },
            'KICK20': { type: 'percent', value: 20, min_order: 20000 },
            'FREE1000': { type: 'fixed', value: 1000, min_order: 5000 },
            'SUMMER15': { type: 'percent', value: 15, min_order: 15000 }
        };

        // Oldal betöltésekor
        document.addEventListener('DOMContentLoaded', function() {
            displayCart();
            updateCartCount();
            
            // LocalStorage-ból kupon betöltése
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
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                itemsHtml += `
                    <div class="cart-item" data-index="${index}">
                        <div class="cart-item-image">
                            <img src="uploads/${item.image || 'default.jpg'}" alt="${item.name}">
                        </div>
                        <div class="cart-item-details">
                            <h3>${item.name}</h3>
                            <div class="cart-item-price">${item.price.toLocaleString()} Ft / db</div>
                        </div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="99" onchange="setQuantity(${index}, this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="cart-item-total">
                            ${itemTotal.toLocaleString()} Ft
                        </div>
                        <button class="remove-item" onclick="removeItem(${index})" title="Eltávolítás">🗑️</button>
                    </div>
                `;
            });
            
            // Kedvezmény számítás
            let discount = 0;
            if(appliedCoupon) {
                const coupon = coupons[appliedCoupon.code];
                if(coupon) {
                    if(coupon.type === 'percent') {
                        discount = Math.round(subtotal * (coupon.value / 100));
                    } else {
                        discount = coupon.value;
                    }
                }
            }
            
            const shipping = subtotal >= 30000 ? 0 : 1990;
            const total = subtotal + shipping - discount;
            
            cartContent.innerHTML = `
                <div class="cart-items">
                    ${itemsHtml}
                </div>
                <div class="cart-summary">
                    <h2>Összegzés</h2>
                    
                    <div class="coupon-section">
                        ${appliedCoupon ? `
                            <div class="coupon-applied">
                                <span>✅ Kupon: ${appliedCoupon.code} (${discount.toLocaleString()} Ft kedvezmény)</span>
                                <span class="remove-coupon" onclick="removeCoupon()">✕</span>
                            </div>
                        ` : `
                            <div class="coupon-input-group">
                                <input type="text" id="couponCode" class="coupon-input" placeholder="Van kuponkódod?">
                                <button class="apply-coupon" onclick="applyCoupon()">Alkalmaz</button>
                            </div>
                        `}
                    </div>
                    
                    <div class="summary-row">
                        <span>Részösszeg:</span>
                        <span>${subtotal.toLocaleString()} Ft</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Szállítás:</span>
                        <span>${shipping === 0 ? 'Ingyenes' : shipping.toLocaleString() + ' Ft'}</span>
                    </div>
                    
                    ${discount > 0 ? `
                        <div class="summary-row" style="color: var(--success-color);">
                            <span>Kedvezmény:</span>
                            <span>-${discount.toLocaleString()} Ft</span>
                        </div>
                    ` : ''}
                    
                    <div class="summary-row total grand-total">
                        <span>Végösszeg:</span>
                        <span>${total.toLocaleString()} Ft</span>
                    </div>
                    
                    <button class="checkout-btn" onclick="proceedToCheckout()" ${cart.length === 0 ? 'disabled' : ''}>
                        Tovább a pénztárhoz
                    </button>
                    
                    <div class="cart-actions">
                        <button class="clear-cart" onclick="clearCart()">Kosár ürítése</button>
                        <a href="products.php" class="continue-shopping">Vásárlás folytatása</a>
                    </div>
                </div>
            `;
        }

        // Mennyiség frissítése
        function updateQuantity(index, change) {
            if (cart[index]) {
                const newQuantity = cart[index].quantity + change;
                if (newQuantity >= 1 && newQuantity <= 99) {
                    cart[index].quantity = newQuantity;
                    saveCart();
                    displayCart();
                }
            }
        }

        // Mennyiség beállítása
        function setQuantity(index, value) {
            const newQuantity = parseInt(value);
            if (newQuantity >= 1 && newQuantity <= 99) {
                cart[index].quantity = newQuantity;
                saveCart();
                displayCart();
            }
        }

        // Termék eltávolítása
        function removeItem(index) {
            if (confirm('Biztosan eltávolítod ezt a terméket a kosárból?')) {
                const itemName = cart[index].name;
                cart.splice(index, 1);
                saveCart();
                displayCart();
                updateCartCount();
                showNotification(`"${itemName}" eltávolítva a kosárból`, 'success');
            }
        }

        // Kosár ürítése
        function clearCart() {
            if (cart.length > 0 && confirm('Biztosan üríteni szeretnéd a kosarat?')) {
                cart = [];
                appliedCoupon = null;
                localStorage.removeItem('appliedCoupon');
                saveCart();
                displayCart();
                updateCartCount();
                showNotification('Kosár kiürítve', 'success');
            }
        }

        // Kosár mentése
        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        // Kosár számláló frissítése
        function updateCartCount() {
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Kupon alkalmazása
        function applyCoupon() {
            const code = document.getElementById('couponCode').value.toUpperCase().trim();
            
            if(!code) {
                showNotification('Kérlek adj meg egy kuponkódot!', 'error');
                return;
            }
            
            const coupon = coupons[code];
            
            if(!coupon) {
                showNotification('Érvénytelen kuponkód!', 'error');
                return;
            }
            
            // Minimum rendelési összeg ellenőrzés
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            if(coupon.min_order && subtotal < coupon.min_order) {
                showNotification(`A kupon használatához minimum ${coupon.min_order.toLocaleString()} Ft értékű rendelés szükséges!`, 'error');
                return;
            }
            
            appliedCoupon = {
                code: code,
                ...coupon
            };
            
            localStorage.setItem('appliedCoupon', JSON.stringify(appliedCoupon));
            displayCart();
            showNotification('Kupon sikeresen alkalmazva!', 'success');
        }

        // Kupon eltávolítása
        function removeCoupon() {
            appliedCoupon = null;
            localStorage.removeItem('appliedCoupon');
            displayCart();
            showNotification('Kupon eltávolítva', 'success');
        }

        // Tovább a pénztárhoz
        function proceedToCheckout() {
            if(cart.length === 0) {
                showNotification('A kosár üres!', 'error');
                return;
            }
            
            // Kosár adatok átadása a checkout oldalnak
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';
            
            const cartInput = document.createElement('input');
            cartInput.type = 'hidden';
            cartInput.name = 'cart_data';
            cartInput.value = JSON.stringify(cart);
            
            const couponInput = document.createElement('input');
            couponInput.type = 'hidden';
            couponInput.name = 'coupon_data';
            couponInput.value = JSON.stringify(appliedCoupon);
            
            form.appendChild(cartInput);
            form.appendChild(couponInput);
            document.body.appendChild(form);
            form.submit();
        }

        // Értesítés megjelenítése
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>
