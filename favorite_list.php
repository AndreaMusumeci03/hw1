<?php
$page_title = "I Miei Preferiti - The North Face";
$current_page = "Favorites";

include 'header.php';
?>
<script src="js/favorite.js" defer></script>
<main>
    <div class="favorites-container">
        <div class="favorites-header">
            <h1>I Miei Preferiti</h1>
            <div class="favorites-count">
                <span class="count">0</span> prodotti
            </div>
        </div>
        
        <div class="favorites-grid" id="favorites-grid">
            <div class="loading-message">Caricamento preferiti...</div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadFavorites();
});

function loadFavorites() {
    fetch('favorite.php?action=get')
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('favorites-grid').innerHTML = 
                '<div class="error-message">Errore: ' + data.error + '</div>';
            return;
        }
        
        displayFavorites(data);
    })
    .catch(error => {
        console.error('Errore:', error);
        document.getElementById('favorites-grid').innerHTML = 
            '<div class="error-message">Errore nel caricamento dei preferiti</div>';
    });
}

function displayFavorites(data) {
    const grid = document.getElementById('favorites-grid');
    const countElement = document.querySelector('.count');
    
    countElement.textContent = data.count;
    
    if (!data.items || data.items.length === 0) {
        grid.innerHTML = '<div class="empty-favorites">Nessun prodotto nei preferiti</div>';
        return;
    }
    
    let html = '';
    data.items.forEach(item => {
        html += `
            <div class="favorite-item">
                <div class="item-image">
                    <img src="${item.image_url}" alt="${item.name}" onerror="this.src='images/placeholder.jpg'">
                </div>
                <div class="item-info">
                    <h3>${item.name}</h3>
                    <p class="item-price">€${item.price.toFixed(2)}</p>
                    <div class="item-actions">
                        <button class="add-to-cart-btn" onclick="addToCart(${item.product_id})">
                            Aggiungi al carrello
                        </button>
                        <button class="remove-favorite-btn" onclick="removeFromFavorites(${item.product_id})">
                            Rimuovi dai preferiti
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    grid.innerHTML = html;
}

function addToCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('cart.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Prodotto aggiunto al carrello!');
        } else {
            alert('Errore nell\'aggiunta al carrello');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
    });
}

function removeFromFavorites(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch('favorite.php?action=remove', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Prodotto rimosso dai preferiti!');
            // Ricarica la lista dei preferiti per aggiornare la vista
            loadFavorites();
        } else {
            alert('Errore nella rimozione dai preferiti: ' + (data.error || 'Errore sconosciuto'));
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore nella rimozione dai preferiti');
    });
}

</script>

<style>

#main {
    position:sticky;
}
.favorites-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    margin-top: 100px;
}

.favorites-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #ccc;
    padding-bottom: 20px;
}

.favorites-header h1 {
    font-size: 2rem;
    color: #333;
}

.favorites-count {
    font-size: 1.2rem;
    color: #666;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.favorite-item {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.favorite-item:hover {
    transform: translateY(-5px);
}

.item-image {
    height: 200px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    padding: 15px;
}

.item-info h3 {
    margin: 0 0 10px 0;
    font-size: 1.2rem;
    color: #333;
}

.item-price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 15px;
}

.item-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}

.add-to-cart-btn, .remove-favorite-btn {
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
}

.add-to-cart-btn {
    background-color: #007bff;
    color: white;
}

.add-to-cart-btn:hover {
    background-color: #0056b3;
}

.remove-favorite-btn {
    background-color: #dc3545;
    color: white;
}

.remove-favorite-btn:hover {
    background-color: #c82333;
}

.loading-message, .error-message, .empty-favorites {
    text-align: center;
    padding: 40px;
    color: #666;
    font-size: 1.2rem;
    grid-column: 1 / -1;
}

.error-message {
    color: #e74c3c;
}
</style>