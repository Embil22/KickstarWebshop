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
        
        const existingItem = cart.find(item => item.id === productId);
        
        if(existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 1
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        
        // Animáció
        this.textContent = 'Hozzáadva!';
        setTimeout(() => {
            this.textContent = 'Kosárba';
        }, 1000);
    });
});

// Kosár oldal funkcionalitás
if(window.location.pathname.includes('cart.php')) {
    displayCart();
}

function displayCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    
    if(cart.length === 0) {
        cartItems.innerHTML = '<p>A kosár üres</p>';
        return;
    }
    
    let total = 0;
    let html = '';
    
    cart.forEach(item => {
        total += item.price * item.quantity;
        html += `
            <div class="cart-item">
                <h4>${item.name}</h4>
                <p>Mennyiség: ${item.quantity}</p>
                <p>Ár: ${(item.price * item.quantity).toLocaleString()} Ft</p>
                <button class="remove-item" data-id="${item.id}">Eltávolítás</button>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    cartTotal.textContent = total.toLocaleString() + ' Ft';
}

updateCartCount();