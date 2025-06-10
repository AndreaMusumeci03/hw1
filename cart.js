document.addEventListener('DOMContentLoaded', function() {
    const cartToggle = document.getElementById('cartToggle');
    const rightShowContainer = document.getElementById('rightShowContainer');
    const cartContent = document.getElementById('cartContent');
    const totalPriceElement = document.getElementById('total-price');
    const cartCountElement = document.querySelector('.cart-count');
    

    if (cartToggle && rightShowContainer) {
        cartToggle.addEventListener('click', function() {
            rightShowContainer.style.display = rightShowContainer.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    window.loadCart = function() {
        fetch('cart.php?action=get', {
            credentials: 'include' // IMPORTANTE per la sessione
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Dati carrello ricevuti:', data);
            if (data.error) {
                console.log('Utente non autenticato, carrello vuoto');
                updateCartUI({ items: [], total: 0, count: 0 });
            } else {
                updateCartUI(data);
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            updateCartUI({ items: [], total: 0, count: 0 });
        });
    }
    
    function updateCartUI(data) {
        if (!cartContent || !totalPriceElement || !cartCountElement) return;
        
        console.log('Aggiornamento UI carrello:', data);
        
        cartContent.innerHTML = '';
        
        if (!data.items || data.items.length === 0) {
            cartContent.innerHTML = '<p>Il tuo carrello è vuoto</p>';
            totalPriceElement.textContent = '€ 0.00';
            cartCountElement.textContent = '0';
            return;
        }
        
        data.items.forEach(item => {
            const price = typeof item.price === 'string' ? 
                         parseFloat(item.price.replace(',', '.')) : 
                         Number(item.price);
            
            if (isNaN(price)) {
                console.error('Prezzo non valido per il prodotto:', item);
                return;
            }
            
            const quantity = Number(item.quantity) || 1;
            const subtotal = price * quantity;
            
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.dataset.productId = item.product_id;
            
            cartItem.innerHTML = `
                <div class="product-image">
                    <img src="${item.image_url || 'placeholder.jpg'}" alt="${item.name}">
                </div>
                <div class="product-info">
                    <h4>${item.name}</h4>
                    <p>€ ${price.toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button class="decrease-qty">-</button>
                        <input type="number" value="${quantity}" min="1" class="quantity-input">
                        <button class="increase-qty">+</button>
                    </div>
                </div>
                <div class="product-subtotal">
                    <p>€ ${subtotal.toFixed(2)}</p>
                    <button class="remove-item">Rimuovi</button>
                </div>
            `;
            
            cartContent.appendChild(cartItem);
        });
        
        const total = typeof data.total === 'string' ? 
                     parseFloat(data.total.replace(',', '.')) : 
                     Number(data.total);
        totalPriceElement.textContent = `€ ${isNaN(total) ? '0.00' : total.toFixed(2)}`;
        cartCountElement.textContent = data.count || '0';
        
        // Event listeners per i pulsanti del carrello
        setupCartEventListeners();
    }
    
    // Funzione separata per gli event listeners del carrello
    function setupCartEventListeners() {
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.closest('.cart-item').dataset.productId;
                removeFromCart(productId);
            });
        });
        
        document.querySelectorAll('.decrease-qty').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.nextElementSibling;
                const newQty = parseInt(input.value) - 1;
                if (newQty >= 1) {
                    input.value = newQty;
                    updateCartItem(input.closest('.cart-item').dataset.productId, newQty);
                }
            });
        });
        
        document.querySelectorAll('.increase-qty').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const newQty = parseInt(input.value) + 1;
                input.value = newQty;
                updateCartItem(input.closest('.cart-item').dataset.productId, newQty);
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const newQty = parseInt(this.value);
                if (newQty >= 1) {
                    updateCartItem(this.closest('.cart-item').dataset.productId, newQty);
                } else {
                    this.value = 1;
                }
            });
        });
    }
    
    
    function removeFromCart(productId) {
        fetch('cart.php?action=remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`,
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function updateCartItem(productId, quantity) {
        fetch('cart.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`,
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            console.log("Tentativo di aggiunta prodotto ID:", productId);
            
            fetch('cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`,
                credentials: 'include'
            })
            .then(response => {
                if (!response.ok) throw new Error('Errore di rete');
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert("Errore: " + data.error);
                } else {
                    alert("Prodotto aggiunto con successo!");
                    loadCart();
                }
            })
            .catch(error => {
                console.error("Errore:", error);
                alert("Si è verificato un errore durante l'aggiunta al carrello");
            });
        });
    });
});