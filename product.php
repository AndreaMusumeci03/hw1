<?php
$page_title = "Dettagli Prodotto - The North Face";
$current_page = "product";

include 'header.php';

// Ottieni l'ID del prodotto dall'URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DEBUG: Aggiungi informazioni per il debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($product_id < 1) {
    header('Location: search.php');
    exit;
}

// Funzione per ottenere i dettagli del prodotto usando MySQLi
function getProductDetails($id) {
    // Configurazione database - modifica questi valori con le tue credenziali
    $host = 'localhost';
    $dbname = 'thenorthface_db';
    $username = 'root'; 
    $password = '';
    
    $mysqli = new mysqli($host, $username, $password, $dbname);
    
    if ($mysqli->connect_error) {
        error_log("Errore di connessione al database: " . $mysqli->connect_error);
        return getProductDetailsFallback($id);
    }
    
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        error_log("Errore nella preparazione della query: " . $mysqli->error);
        $mysqli->close();
        return getProductDetailsFallback($id);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("Errore nell'esecuzione della query: " . $stmt->error);
        $stmt->close();
        $mysqli->close();
        return getProductDetailsFallback($id);
    }
    
    $product = $result->fetch_assoc();
    
    $stmt->close();
    $mysqli->close();
    
    if (!$product) {
        return null;
    }
    
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => (float)$product['price'],
        'original_price' => isset($product['original_price']) ? (float)$product['original_price'] : null,
        'category' => $product['category'],
        'color' => $product['color'],
        'sizes' => isset($product['size']) ? explode(',', $product['size']) : ['Unica'],
        'description' => $product['description'],
        'long_description' => isset($product['long_description']) ? $product['long_description'] : $product['description'],
        'features' => isset($product['features']) ? explode('|', $product['features']) : [
            'Prodotto di qualità superiore',
            'Materiali selezionati',
            'Design moderno e funzionale',
            'Garanzia del produttore'
        ],
        'images' => [
            $product['image_url'],
            // Aggiungi più immagini se hai una tabella separata per le immagini del prodotto
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600',
            'https://images.unsplash.com/photo-1606237705104-75f1a5b28806?w=600'
        ],
        'source' => 'internal',
        'stock' => isset($product['stock']) ? (bool)$product['stock'] : true,
        'rating' => isset($product['rating']) ? (float)$product['rating'] : 4.5,
        'reviews_count' => isset($product['reviews_count']) ? (int)$product['reviews_count'] : 50
    ];
}

$product = getProductDetails($product_id);

if (!$product) {
    $product = getProductDetailsFallback($product_id);
}

if (!$product) {
    $page_title = "Prodotto non trovato - The North Face";
}
?>

<main>
    <link rel="stylesheet" href="<?php echo getenv('BASE_URL'); ?>/Apps/public/css/index.css">
    <script src="favorite.js" defer></script>
    
    <?php if ($product): ?>
    <div class="product-container">
        <div class="breadcrumb">
            <span><a href="index.php">Home</a> / <a href="search.php">Prodotti</a> / <?php echo htmlspecialchars($product['name']); ?></span>
        </div>
        
        <div class="product-main">
            <div class="product-gallery">
                <div class="main-image">
                    <img id="mainImage" src="<?php echo $product['images'][0]; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <button class="favorite-btn-large" 
                            data-product-id="<?php echo $product['id']; ?>"
                            onclick="toggleFavorite(<?php echo $product['id']; ?>, this)">
                        🤍
                    </button>
                </div>
                
                <div class="thumbnail-gallery">
                    <?php foreach ($product['images'] as $index => $image): ?>
                    <img class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                         src="<?php echo $image; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onclick="changeMainImage('<?php echo $image; ?>', this)">
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="product-details">
                <div class="product-header">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-meta">
                        <span class="category"><?php echo htmlspecialchars($product['category']); ?></span>
                        <?php if (isset($product['rating'])): ?>
                        <div class="rating">
                            <span class="stars"><?php echo str_repeat('★', floor($product['rating'])) . str_repeat('☆', 5 - floor($product['rating'])); ?></span>
                            <span class="rating-text">(<?php echo $product['reviews_count']; ?> recensioni)</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="price-section">
                    <span class="current-price">€<?php echo number_format($product['price'], 2); ?></span>
                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                    <span class="original-price">€<?php echo number_format($product['original_price'], 2); ?></span>
                    <span class="discount">-<?php echo round((1 - $product['price']/$product['original_price']) * 100); ?>%</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-options">
                    <?php if (!empty($product['color'])): ?>
                    <div class="option-group">
                        <label>Colore: <strong><?php echo htmlspecialchars($product['color']); ?></strong></label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['sizes'])): ?>
                    <div class="option-group">
                        <label>Taglia:</label>
                        <div class="size-selector">
                            <?php foreach ($product['sizes'] as $size): ?>
                            <button type="button" class="size-btn" data-size="<?php echo $size; ?>"><?php echo $size; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="quantity-section">
                        <label>Quantità:</label>
                        <div class="quantity-selector">
                            <button type="button" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="10">
                            <button type="button" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button class="add-to-cart-btn-large" onclick="addToCart(<?php echo $product['id']; ?>)">
                        Aggiungi al Carrello
                    </button>
                    <button class="buy-now-btn" onclick="buyNow(<?php echo $product['id']; ?>)">
                        Compra Ora
                    </button>
                </div>
                
                <div class="product-info">
                    <div class="info-item">
                        <strong>Disponibilità:</strong> 
                        <span class="<?php echo $product['stock'] ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] ? 'Disponibile' : 'Non disponibile'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Codice:</strong> TNF-<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div class="info-item">
                        <strong>Fonte:</strong> 
                        <span class="source-info <?php echo $product['source']; ?>">
                            <?php echo $product['source'] === 'external' ? 'Partner' : 'Store TNF'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="product-sections">
            <div class="section-tabs">
                <button class="tab-btn active" onclick="showTab('description')">Descrizione</button>
                <button class="tab-btn" onclick="showTab('features')">Caratteristiche</button>
                <button class="tab-btn" onclick="showTab('reviews')">Recensioni</button>
            </div>
            
            <div class="tab-content">
                <div id="description" class="tab-panel active">
                    <h3>Descrizione Prodotto</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['long_description'])); ?></p>
                </div>
                
                <div id="features" class="tab-panel">
                    <h3>Caratteristiche Principali</h3>
                    <ul>
                        <?php foreach ($product['features'] as $feature): ?>
                        <li><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div id="reviews" class="tab-panel">
                    <h3>Recensioni Clienti</h3>
                    <div class="reviews-summary">
                        <div class="average-rating">
                            <span class="rating-number"><?php echo $product['rating']; ?></span>
                            <div class="stars-large"><?php echo str_repeat('★', floor($product['rating'])) . str_repeat('☆', 5 - floor($product['rating'])); ?></div>
                            <span><?php echo $product['reviews_count']; ?> recensioni</span>
                        </div>
                    </div>
                    <p><em>Sistema di recensioni in fase di implementazione</em></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <div class="not-found-container">
        <h1>Prodotto non trovato</h1>
        <p>Il prodotto con ID <strong><?php echo htmlspecialchars($product_id); ?></strong> non è disponibile o è stato rimosso.</p>
        <div class="debug-info" style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;">
            <h3>Informazioni per il debug:</h3>
            <p><strong>ID ricevuto:</strong> <?php echo var_export($_GET['id'] ?? 'NESSUNO', true); ?></p>
            <p><strong>ID convertito:</strong> <?php echo var_export($product_id, true); ?></p>
            <p><strong>IDs prodotti disponibili:</strong> 1, 2, 3, 4, 5, 6, ..., 30</p>
            <p><strong>URLs di test:</strong></p>
            <ul style="text-align: left; max-width: 400px; margin: 0 auto;">
                <li><a href="product.php?id=1">product.php?id=1</a></li>
                <li><a href="product.php?id=2">product.php?id=2</a></li>
                <li><a href="product.php?id=3">product.php?id=3</a></li>
                <li><a href="product.php?id=4">product.php?id=4</a></li>
                <li><a href="product.php?id=5">product.php?id=5</a></li>
                <li><a href="product.php?id=6">product.php?id=6</a></li>
                <li><a href="product.php?id=30">product.php?id=30</a></li>
            </ul>
        </div>
        
        <div class="not-found-actions">
            <a href="search.php" class="back-btn">Torna alla ricerca</a>
            <a href="index.php" class="home-btn">Vai alla Homepage</a>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    function changeMainImage(src, thumbnail) {
        document.getElementById('mainImage').src = src;

        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });

        thumbnail.classList.add('active');
    }
    
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    function changeQuantity(delta) {
        const quantityInput = document.getElementById('quantity');
        const currentValue = parseInt(quantityInput.value);
        const newValue = Math.max(1, Math.min(10, currentValue + delta));
        quantityInput.value = newValue;
    }
    
    function showTab(tabName) {
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.getElementById(tabName).classList.add('active');
        
        event.target.classList.add('active');
    }
    
    function addToCart(productId) {
        const selectedSize = document.querySelector('.size-btn.selected');
        const quantity = document.getElementById('quantity').value;
        
        if (document.querySelectorAll('.size-btn').length > 0 && !selectedSize) {
            alert('Seleziona una taglia');
            return;
        }
        
        const data = {
            product_id: productId,
            quantity: quantity,
            size: selectedSize ? selectedSize.dataset.size : null
        };
        
        console.log('Aggiungi al carrello:', data);
        alert('Prodotto aggiunto al carrello!');
    }
    
    function buyNow(productId) {
        console.log('Acquisto diretto prodotto:', productId);
        alert('Reindirizzamento al checkout...');
    }

    window.goToProduct = function(productId) {
        window.location.href = `product.php?id=${productId}`;
    };
    </script>
    
    <style>


    #main{
        position:sticky;
    }
    
    .product-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .breadcrumb {
        margin: 20px 0;
        color: #666;
    }
    
    .breadcrumb a {
        color: #007bff;
        text-decoration: none;
    }
    
    .product-main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }
    
    /* Galleria Immagini */
    .product-gallery {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .main-image {
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
        border-radius: 8px;
    }
    
    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .favorite-btn-large {
        position: absolute;
        top: 15px;
        right: 15px;
        background: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .thumbnail-gallery {
        display: flex;
        gap: 10px;
        overflow-x: auto;
    }
    
    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        opacity: 0.7;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    
    .thumbnail.active,
    .thumbnail:hover {
        opacity: 1;
        border-color: #007bff;
    }
    
    /* Dettagli Prodotto */
    .product-details {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .product-header h1 {
        margin: 0;
        font-size: 2em;
        color: #333;
    }
    
    .product-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }
    
    .category {
        background: #f0f0f0;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
    }
    
    .rating {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .stars {
        color: #ffc107;
        font-size: 16px;
    }
    
    .price-section {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .current-price {
        font-size: 2em;
        font-weight: bold;
        color: #e74c3c;
    }
    
    .original-price {
        font-size: 1.2em;
        text-decoration: line-through;
        color: #999;
    }
    
    .discount {
        background: #e74c3c;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .option-group {
        margin-bottom: 15px;
    }
    
    .option-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .size-selector {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .size-btn {
        padding: 10px 15px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .size-btn:hover,
    .size-btn.selected {
        border-color: #007bff;
        background: #007bff;
        color: white;
    }
    
    .quantity-section {
        display: flex;
        align-items: center;
        gap: 0;
        width: fit-content;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 0;
        width: fit-content;
    }
    
    .quantity-selector button {
        width: 40px;
        height: 40px;
        border: 1px solid #ddd;
        background: #f8f9fa;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
    }
    
    .quantity-selector input {
        width: 60px;
        height: 40px;
        text-align: center;
        border: 1px solid #ddd;
        border-left: none;
        border-right: none;
        font-size: 16px;
    }
    
    .product-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .add-to-cart-btn-large,
    .buy-now-btn {
        flex: 1;
        padding: 15px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .add-to-cart-btn-large {
        background: #000;
        color: white;
    }
    
    .add-to-cart-btn-large:hover {
        background: #333;
    }
    
    .buy-now-btn {
        background: #007bff;
        color: white;
    }
    
    .buy-now-btn:hover {
        background: #0056b3;
    }
    
    .product-info {
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .info-item {
        margin-bottom: 8px;
    }
    
    .in-stock {
        color: #28a745;
        font-weight: 600;
    }
    
    .out-stock {
        color: #dc3545;
        font-weight: 600;
    }

    .source-info.internal {
        color: #28a745;
        font-weight: 600;
    }

    .source-info.external {
        color: #007bff;
        font-weight: 600;
    }
    
    /* Sezioni Tab */
    .product-sections {
        border-top: 1px solid #eee;
        padding-top: 40px;
    }
    
    .section-tabs {
        display: flex;
        border-bottom: 1px solid #eee;
        margin-bottom: 30px;
    }
    
    .tab-btn {
        padding: 15px 25px;
        background: none;
        border: none;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    
    .tab-btn.active,
    .tab-btn:hover {
        color: #007bff;
        border-bottom-color: #007bff;
    }
    
    .tab-panel {
        display: none;
    }
    
    .tab-panel.active {
        display: block;
    }
    
    .tab-panel h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
    }
    
    .tab-panel ul {
        padding-left: 20px;
    }
    
    .tab-panel li {
        margin-bottom: 8px;
    }
    
    .reviews-summary {
        background: #f8f9fa;
    }    
    
    .average-rating {
        text-align: center;
    }
    
    .rating-number {
        font-size: 3em;
        font-weight: bold;
        color: #333;
    }
    
    .stars-large {
        font-size: 24px;
        color: #ffc107;
        margin: 10px 0;
    }
    
    /* Prodotto non trovato */
    .not-found-container {
        text-align: center;
        padding: 100px 20px;
    }
    
    .back-btn {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 24px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .product-main {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .product-actions {
            flex-direction: column;
        }
        
        .section-tabs {
            overflow-x: auto;
        }
        
        .tab-btn {
            white-space: nowrap;
            padding: 12px 20px;
        }
    }
    </style>
</main>

<?php include 'footer.php'; ?>