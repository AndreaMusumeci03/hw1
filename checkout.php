<?php
$page_title = "Checkout - The North Face";
$current_page = "Checkout";

include 'header.php';
?>

<main>
    <div class="view-container12">

        <div class="cart-container">
            <div class="spn-cart">Carrello</div>
            <div class="num_articoli">
                <span class="num">0</span> articoli
            </div>
        </div>

        <div class="checkout-container">
            <div class="list-items">
                <div class="loading-message" style="text-align: center; padding: 20px; color: #666;">
                    Caricamento prodotti...
                </div>
            </div>
            <div class="Riepilogo">
                <p> Riepilogo ordine</p>
                <div class="subtotale">
                    <span>Subtotale</span>
                    <span class="subtotale-amount">€0.00</span>
                </div>
                <div class="spedizione">
                    <span>Costi di spedizione stimati</span>
                    <span>Gratuita</span>
                </div>
                <div class="reso">Reso gratuito entro 30 giorni</div>
                <div class="tot-ordine">
                    <span>TOTALE ORDINE</span>
                    <span class="totale-amount">€0.00</span>
                </div>
                <div class="procedi">
                    <button id="checkout-btn" class="checkout-btn" >Procedi all'acquisto</button>
                </div>
            </div>
        </div>

    </div>

</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
});

function loadCartItems() {
    fetch('cart.php?action=get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.querySelector('.list-items').innerHTML = 
                '<div class="error-message" style="text-align: center; padding: 20px; color: #e74c3c;">Errore: ' + data.error + '</div>';
            return;
        }
        
        displayCartItems(data);
        updateCartSummary(data);
    })
    .catch(error => {
        console.error('Errore nel caricamento del carrello:', error);
        document.querySelector('.list-items').innerHTML = 
            '<div class="error-message" style="text-align: center; padding: 20px; color: #e74c3c;">Errore nel caricamento del carrello</div>';
    });
}

function displayCartItems(data) {
    const listItems = document.querySelector('.list-items');
    
    if (!data.items || data.items.length === 0) {
        listItems.innerHTML = '<div class="empty-cart" style="text-align: center; padding: 40px; color: #666;">Il tuo carrello è vuoto</div>';
        return;
    }
    
    let itemsHTML = '';
    data.items.forEach(item => {
        itemsHTML += `
            <div class="cart-item" data-product-id="${item.product_id}">
                <div class="item-image">
                    <img src="${item.image_url}" alt="${item.name}" 
                        onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;100&quot; height=&quot;100&quot;><rect width=&quot;100&quot; height=&quot;100&quot; fill=&quot;#eee&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; alignment-baseline=&quot;middle&quot; text-anchor=&quot;middle&quot; font-size=&quot;14&quot; fill=&quot;#aaa&quot; dy=&quot;.3em&quot;&gt;Nessuna immagine&lt;/text&gt;&lt;/svg&gt;';">
                </div>
                <div class="item-details">
                    <h3 class="item-name">${item.name}</h3>
                    <div class="item-price">€${parseFloat(item.price).toFixed(2)}</div>
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
                <div class="item-actions">
                    <div class="item-subtotal">€${parseFloat(item.subtotal).toFixed(2)}</div>
                    <button class="remove-btn" onclick="removeItem(${item.product_id})">Rimuovi</button>
                </div>
            </div>
        `;
    });
    
    listItems.innerHTML = itemsHTML;
}

function updateCartSummary(data) {
    // Aggiorna numero articoli
    document.querySelector('.num').textContent = data.count || 0;
    
    // Aggiorna subtotale e totale
    const total = parseFloat(data.total || 0);
    document.querySelector('.subtotale-amount').textContent = '€' + total.toFixed(2);
    document.querySelector('.totale-amount').textContent = '€' + total.toFixed(2);
    
    // Abilita/disabilita pulsante checkout
    const checkoutBtn = document.getElementById('checkout-btn');
    if (data.count > 0) {
        checkoutBtn.disabled = false;
        checkoutBtn.style.backgroundColor = 'rgba(0, 123, 255, 0.8)';
        checkoutBtn.style.color = 'white';
        checkoutBtn.style.cursor = 'pointer';
    } else {
        checkoutBtn.disabled = true;
        checkoutBtn.style.backgroundColor = 'rgba(114, 114, 114, 0.65)';
        checkoutBtn.style.color = 'rgb(61, 61, 61)';
        checkoutBtn.style.cursor = 'not-allowed';
    }
}

function updateQuantity(productId, newQuantity) {
    if (newQuantity < 0) return;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);
    
    fetch('cart.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartItems(); // Ricarica il carrello
        } else {
            alert('Errore nell\'aggiornamento della quantità');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore nell\'aggiornamento della quantità');
    });
}

function removeItem(productId) {
    if (!confirm('Sei sicuro di voler rimuovere questo articolo dal carrello?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch('cart.php?action=remove', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCartItems(); // Ricarica il carrello
        } else {
            alert('Errore nella rimozione dell\'articolo');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore nella rimozione dell\'articolo');
    });
}

document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'checkout-btn') {
        console.log('Bottone cliccato');
        const btn = e.target;
        btn.disabled = false;
        btn.textContent = 'Reindirizzamento...';
        fetch('stripe.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.url) {
                    window.location.href = data.url;
                } else {
                    alert('Errore Stripe: ' + (data.error || 'Impossibile iniziare il pagamento.'));
                    btn.disabled = false;
                    btn.textContent = "Procedi all'acquisto";
                }
            })
            .catch(err => {
                alert('Errore di rete con Stripe');
                btn.disabled = false;
                btn.textContent = "Procedi all'acquisto";
            });
    }
});
</script>

<style>
#main {
    position:sticky;
}

.view-container12{
    height: 100vh;
    width: 100%;
    background-color: rgb(255, 255, 255);
    display: flex;
    flex-direction: column;
}

.cart-container{
    margin-top: 3vh;
    display: flex;
    top: 0;
    left: 0;
    width: 80%;
    height: 3vh;
    background-color: rgb(255, 255, 255);
    font-size: 0.7vw;
    margin-left: 10vw;
    margin-top: 6vh;
    border-bottom: 1px solid rgb(0, 0, 0);
    
    align-items: center;
    justify-content:space-between;
}

.spn-cart{
    font-size: 1.5vw;
    margin-left: 1vw;
    color: rgb(0, 0, 0);
    
}

.num_articoli{
    margin-right: 1vw;
    color: rgb(0, 0, 0);
    font-size:1vw;
}

.checkout-container{
    width: 80%;
    height: 60vh;
    margin-left: 10vw;
    margin-top: 4vh;
    display: flex;
    
    
    
}

.list-items{
    width: 35vw;
    height: 60vh;
    margin-left: 3vw;
    
    overflow-y: auto;
    
    border: 2px solid rgba(119, 119, 119, 0.71);
    border-radius: 10px;
}

/* Nuovi stili per gli item del carrello */
.cart-item {
    display: flex;
    padding: 15px;
    border-bottom: 1px solid rgba(119, 119, 119, 0.3);
    align-items: center;
    gap: 15px;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.item-name {
    font-size: 0.9vw;
    font-weight: bold;
    margin: 0;
    color: rgb(0, 0, 0);
}

.item-price {
    font-size: 0.8vw;
    color: rgb(85, 85, 85);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.qty-btn {
    width: 25px;
    height: 25px;
    border: 1px solid rgba(119, 119, 119, 0.5);
    background: white;
    cursor: pointer;
    border-radius: 3px;
    font-size: 0.8vw;
}

.qty-btn:hover {
    background: rgba(119, 119, 119, 0.1);
}

.quantity {
    font-size: 0.8vw;
    min-width: 20px;
    text-align: center;
}

.item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.item-subtotal {
    font-weight: bold;
    font-size: 0.9vw;
    color: rgb(0, 0, 0);
}

.remove-btn {
    background: rgba(220, 53, 69, 0.8);
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.7vw;
}

.remove-btn:hover {
    background: rgba(220, 53, 69, 1);
}


.procedi button:not(:disabled):hover {
    background-color: rgba(0, 123, 255, 0.9) !important;
}


.checkout-btn {
    width: 20vw;
    height: 5vh;
    font-size: 1.5vw;
    background-color: rgba(114, 114, 114, 0.65);
    color: rgb(61, 61, 61);
    border: none;
    border-radius: 20px;
    margin-top: 10vh;
    font-size: 1vw;
    font-weight: bold;
    cursor: pointer;
    margin-left: 5vw;
}

.Riepilogo{
    width: 30vw;
    height: 40vh;
    display: flex;
    flex-direction: column;
    border: 2px solid rgba(119, 119, 119, 0.71);
    border-radius: 10px;
    margin-left: 8vw;
}

.Riepilogo p{
    font-size: 1.5vw;
    margin-left: 1vw;
    margin-top: 1vh;
    color: rgb(0, 0, 0);
    font-weight: bold;
}

.subtotale{
    display: flex;
    justify-content: space-between;
    margin-left: 1vw;
    margin-right: 1vw;
    margin-top: 2vh;
}
.subtotale span{
    font-size: 1vw;
    color: rgb(0, 0, 0);
}

.spedizione{
    display: flex;
    justify-content: space-between;
    margin-left: 1vw;
    margin-right: 1vw;
    margin-top: 5vh;
}

.reso{
    font-size: 1vw;
    color: rgb(0, 0, 0);
    margin-left: 1vw;
    margin-top: 5vh;
}

.tot-ordine{
    display: flex;
    justify-content: space-between;
    margin-left: 1vw;
    margin-right: 1vw;
    margin-top: 8vh;
}

.spedizione span{
    font-size: 1vw;
    color: rgb(0, 0, 0);
}

.tot-ordine span{
    font-size: 1vw;
    color: rgb(0, 0, 0);
    font-weight: bold;
}

.right-showcontainer{
    overflow-y: auto;
}
</style>