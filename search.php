<?php
$page_title = "Ricerca - The North Face";
$current_page = "search";

include 'header.php';

$search_term = $_GET['q'] ?? '';
?>

<main>
    <link rel="stylesheet" href="<?php echo getenv('BASE_URL'); ?>index.css">
    <script src="favorite.js" defer></script>
    <div class="view-container23">
        <div class="percorso">
            <span>Home/Ricerca<?php echo $search_term ? '/'. htmlspecialchars($search_term) : ''; ?></span>
        </div>
        <div class="open">
            <h1 id="search-title">
                <?php echo $search_term ? 'Risultati per "' . htmlspecialchars($search_term) . '"' : 'Ricerca prodotti'; ?>
            </h1>
            <p id="search-subtitle">Scopri i nostri prodotti che corrispondono alla tua ricerca.</p>
            

            <div class="search-container-page">
                <input type="text" id="searchInput" placeholder="Cerca prodotti..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button id="searchBtn">Cerca</button>
            </div>
            
            <div class="filtro_btn_container" id="scroll">
                <div class="filtro_btn">
                    <button>
                        <span class="spn_btn">Prezzo</span>
                        <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
                    </button>
                </div>
                <div class="filtro_btn">
                    <button>
                        <span class="spn_btn">Modello</span>
                        <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
                    </button>
                </div>
                <div class="filtro_btn">
                    <button>
                        <span class="spn_btn">Colore</span>
                        <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
                    </button>
                </div>
                <div class="filtro_btn">
                    <button>
                        <span class="spn_btn">Fonte</span>
                        <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
                    </button>
                </div>
                
                <div class="right-sort">
                    <div class="sort_btn">
                        <button>
                            <span class="spn_srt">Ordina per:</span>
                            <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        

        <div id="loading" class="loading-indicator" style="display: none;">
            <p>Caricamento prodotti...</p>
        </div>
        

        <div class="view-content" id="searchResults">

        </div>

        <button class="more-view" id="loadMoreBtn" style="display: none;">
            <span class="spn_more">Mostra altro</span>
            <i><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></i>
        </button>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const searchResults = document.getElementById('searchResults');
    const loading = document.getElementById('loading');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const searchTitle = document.getElementById('search-title');
    const searchSubtitle = document.getElementById('search-subtitle');
    
    let currentPage = 1;
    let currentQuery = '';
    let isLoading = false;
    let hasMoreResults = true;
    let abortController = null;

   
    window.goToProduct = function(productId) {
    console.log('Navigando al prodotto con ID:', productId);
    if (productId) {

        if (typeof productId === 'string' && productId.startsWith('ext_')) {

            alert('Questo è un prodotto partner. Dettagli completi non disponibili.');
            return;
        }
        window.location.href = `product.php?id=${productId}`;
    } else {
        console.error('ID prodotto non valido:', productId);
    }
};

    window.addToCartFromSearch = function(productId) {
        console.log('Aggiungendo al carrello prodotto ID:', productId);
    
    fetch('cart.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${encodeURIComponent(productId)}&quantity=1`,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Prodotto aggiunto al carrello!');
            if (typeof loadCart === 'function') loadCart();
        } else {
            alert('Errore: ' + (data.error || data.message || 'Impossibile aggiungere al carrello'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore di connessione');
    });
};


    function performSearch(query, page = 1, append = false) {

        if (isLoading) {
            console.log('Ricerca già in corso, ignorata');
            return Promise.resolve();
        }
        
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();
        
        isLoading = true;
        loading.style.display = 'block';
        
        if (!append) {
            searchResults.innerHTML = '';
            currentPage = 1;
            hasMoreResults = true;
            loadMoreBtn.style.display = 'none';
        }
        
        currentQuery = query;
        
        if (!append) {
            if (query) {
                searchTitle.textContent = `Risultati per "${query}"`;
                searchSubtitle.textContent = 'Prodotti trovati nella nostra collezione e cataloghi partner.';
                const newUrl = `search.php?q=${encodeURIComponent(query)}`;
                if (window.location.search !== `?q=${encodeURIComponent(query)}`) {
                    window.history.pushState({}, '', newUrl);
                }
            } else {
                searchTitle.textContent = 'Ricerca prodotti';
                searchSubtitle.textContent = 'Scopri i nostri prodotti che corrispondono alla tua ricerca.';
            }
        }
        
        console.log('Eseguendo ricerca per:', query, 'Pagina:', page, 'Append:', append);
        
        return fetch('search_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `query=${encodeURIComponent(query)}&page=${page}`,
            credentials: 'include',
            signal: abortController.signal
        })
        .then(response => {
            console.log('Status della risposta:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text().then(text => {
                console.log('Testo della risposta (primi 200 char):', text.substring(0, 200));
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Errore nel parsing JSON:', e);
                    console.error('Risposta completa:', text);
                    throw new Error('Risposta non valida dal server');
                }
            });
        })
        .then(data => {
            console.log('Dati ricevuti:', data);
            
            if (data.success) {
                const products = data.products || [];
                
                displayResults(products, append);

                if (page > 1) {
                    hasMoreResults = products.length >= 12 && data.has_more === true;
                } else {
                    hasMoreResults = data.has_more === true && products.length > 0;
                }
                
                if (page >= 10) {
                    hasMoreResults = false;
                }
                
                loadMoreBtn.style.display = hasMoreResults ? 'block' : 'none';
                
                console.log('HasMoreResults:', hasMoreResults, 'Products count:', products.length, 'Page:', page);
                
            } else {
                showError(data.message || 'Errore durante la ricerca');
                hasMoreResults = false;
                loadMoreBtn.style.display = 'none';
            }
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                console.log('Richiesta cancellata');
                return;
            }
            
            console.error('Errore completo:', error);
            
            let errorMessage = 'Si è verificato un errore durante la ricerca';
            if (error.message.includes('404')) {
                errorMessage = 'API non trovata - controlla il percorso del file search_api.php';
            } else if (error.message.includes('500')) {
                errorMessage = 'Errore del server - controlla i log PHP';
            } else if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
                errorMessage = 'Errore di connessione - controlla la configurazione del server';
            }
            
            showError(errorMessage);
            hasMoreResults = false;
            loadMoreBtn.style.display = 'none';
        })
        .finally(() => {
            isLoading = false;
            loading.style.display = 'none';
            abortController = null;
        });
    }


    function displayResults(products, append = false) {
        const resultsContainer = document.getElementById('searchResults');
        
        if (!append) {
            resultsContainer.innerHTML = '';
        }
        
        if (!products || products.length === 0) {
            if (!append) {
                resultsContainer.innerHTML = `
                    <div class="no-results">
                        <h3>Nessun risultato trovato</h3>
                        <p>Prova a modificare i termini di ricerca o sfoglia le nostre categorie.</p>
                    </div>
                `;
            }
            return;
        }
        
        const fragment = document.createDocumentFragment();
        
        products.forEach(product => {
            const productElement = createProductCard(product);
            fragment.appendChild(productElement);
        });
        
        resultsContainer.appendChild(fragment);
        
        if (append && products.length > 0) {
            setTimeout(() => {
                const allItems = resultsContainer.querySelectorAll('.item');
                const newItems = Array.from(allItems).slice(-products.length);
                if (newItems.length > 0) {
                    newItems[0].scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            }, 100);
        }
    }

    function createProductCard(product) {
    const productDiv = document.createElement('div');
    productDiv.className = 'item';
    
    const imageUrl = product.image || 'https://via.placeholder.com/300x200?text=No+Image';
    const productName = product.name || 'Prodotto senza nome';
    const productPrice = product.price ? `€${product.price.toFixed(2)}` : 'Prezzo non disponibile';
    const sourceClass = product.source === 'external' ? 'external' : 'internal';
    const sourceText = product.source === 'external' ? 'Partner' : 'Store';
    
    productDiv.innerHTML = `
        <div class="img_grid">
            <button class="favorite-btn" 
                    data-product-id="${product.id}"
                    onclick="event.stopPropagation(); toggleFavorite(${product.id}, this)">
                🤍
            </button>
            <!-- Immagine cliccabile per andare alla pagina prodotto -->
            <img src="${imageUrl}" 
                 alt="${productName}" 
                 loading="lazy"
                 onclick="goToProduct(${product.id})"
                 style="cursor: pointer;"
                 onload="this.classList.add('loaded')"
                 onerror="handleImageError(this)">
            <div class="source-badge ${sourceClass}">${sourceText}</div>
        </div>
        <div class="txt_grid">
            <div class="item_txt">
                <!-- Titolo cliccabile -->
                <h3 onclick="goToProduct(${product.id})" style="cursor: pointer; color: #007bff;">
                    ${productName}
                </h3>
                <p class="price">${productPrice}</p>
                ${product.category ? `<p class="category">${product.category}</p>` : ''}
                ${product.color ? `<span class="color-info">Colore: ${product.color}</span>` : ''}
                ${product.size ? `<span class="size-info">Taglia: ${product.size}</span>` : ''}
            </div>
            <div class="item_actions">
                <!-- Pulsante "Vedi Dettagli" -->
                <button class="view-details-btn" onclick="goToProduct(${product.id})">
                    Vedi Dettagli
                </button>
                <button class="add-to-cart-btn" 
                    data-product-id="${product.id}" 
                    data-source="${product.source || 'internal'}">
                    Aggiungi al Carrello
                </button>
            </div>
        </div>
    `;
    
    setTimeout(() => {
        const favoriteBtn = productDiv.querySelector('.favorite-btn');
        const productImage = productDiv.querySelector('.product-image');
        const productTitle = productDiv.querySelector('.product-title');
        const viewDetailsBtn = productDiv.querySelector('.view-details-btn');
        const addToCartBtn = productDiv.querySelector('.add-to-cart-btn');
        
        if (favoriteBtn) {
            favoriteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (typeof toggleFavorite === 'function') {
                    toggleFavorite(product.id, this);
                }
            });
        }
        
        if (productImage) {
            productImage.addEventListener('click', function() {
                window.location.href = `product.php?id=${product.id}`;
            });
            
            productImage.addEventListener('error', function() {
                this.src = 'https://via.placeholder.com/300x200?text=Immagine+Non+Disponibile';
                this.alt = 'Immagine non disponibile';
            });
        }
        
        if (productTitle) {
            productTitle.addEventListener('click', function() {
                window.location.href = `product.php?id=${product.id}`;
            });
        }
        
        if (viewDetailsBtn) {
            viewDetailsBtn.addEventListener('click', function() {
                window.location.href = `product.php?id=${product.id}`;
            });
        }
        
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                addToCartFromSearch(product.id);
            });
        }
    }, 0);
    
    return productDiv;
}


    

     document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            const btn = e.target.closest('.add-to-cart-btn');
            const productId = btn.dataset.productId;
            const source = btn.dataset.source;

            fetch('cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Prodotto aggiunto al carrello!');
                    if (typeof loadCart === 'function') loadCart();
                } else {
                    alert('Errore: ' + (data.error || 'Impossibile aggiungere al carrello'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore di connessione');
            });
        }
    });
 
    window.handleImageError = function(img) {
        img.classList.add('loaded');
        img.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" viewBox="0 0 300 200"><rect width="300" height="200" fill="%23f0f0f0"/><text x="150" y="100" text-anchor="middle" dy=".3em" fill="%23999" font-size="16">Immagine non disponibile</text></svg>';
    };

    function showError(message) {
        const resultsContainer = document.getElementById('searchResults');
        resultsContainer.innerHTML = `
            <div class="error-message">
                <h3>Si è verificato un errore</h3>
                <p>${message}</p>
            </div>
        `;
    }

    window.addToCart = function(productId, source) {
        console.log('Aggiunto al carrello:', productId, 'da', source);
        alert('Prodotto aggiunto al carrello!');
    };

    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            performSearch(query);
        } else if (query.length === 0) {
            performSearch('');
        } else {
            alert('Inserisci almeno 2 caratteri per la ricerca.');
        }
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query.length >= 2) {
                performSearch(query);
            } else if (query.length === 0) {
                performSearch('');
            } else {
                alert('Inserisci almeno 2 caratteri per la ricerca.');
            }
        }
    });

    loadMoreBtn.addEventListener('click', function() {
        console.log('Click su Mostra altro - Stato attuale:', {
            isLoading,
            hasMoreResults,
            currentPage,
            currentQuery
        });
        
        if (isLoading) {
            console.log('Caricamento in corso - click ignorato');
            return;
        }
        
        if (!hasMoreResults) {
            console.log('Nessun risultato aggiuntivo - nascondo pulsante');
            this.style.display = 'none';
            return;
        }
        
        if (currentPage >= 10) {
            console.log('Limite massimo pagine raggiunto');
            hasMoreResults = false;
            this.style.display = 'none';
            return;
        }
        
        currentPage++;
        console.log('Caricamento pagina:', currentPage);
        
        this.disabled = true;
        const originalContent = this.innerHTML;
        this.innerHTML = '<span class="spn_more">Caricamento...</span>';
        
        performSearch(currentQuery, currentPage, true)
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalContent;
            });
    });


    window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialQuery = urlParams.get('q') || '';
        
        if (initialQuery) {
            searchInput.value = initialQuery;
            performSearch(initialQuery);
        } else {
            performSearch('');
        }
    });


    



    
    
});
    </script>
    
    <style>
        .search-container-page {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            max-width: 500px;
        }
        
        .search-container-page input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-container-page button {
            padding: 12px 24px;
            background: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .search-container-page button:hover {
            background: #333;
        }
        
        .loading-indicator {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }
        
        .no-results, .error-message {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-results h3, .error-message h3 {
            margin-bottom: 16px;
            color: #333;
        }
        
        .source-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            z-index: 1;
        }
        
        .source-badge.internal {
            background: #2ed573;
            color: white;
        }
        
        .source-badge.external {
            background: #3742fa;
            color: white;
        }
        
        .img_grid {
            position: relative;
        }
        
        .img_grid img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .add-to-cart-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .search-container-page {
                flex-direction: column;
            }
        }


.view-container23 {
    display: flex;
    flex-direction: column;
}

.more-view {
    margin-top: 30px;
    margin-bottom: 20px;
    align-self: center;
    width: fit-content;
    min-width: 200px;
}

.view-content .more-view {
    display: none !important;
}

.more-view {
    grid-column: 1 / -1;
    justify-self: center;
    margin: 20px 0;
}
.view-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px 0;
    width: 80%;
}

.item {
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    height: 420px;
    transition: transform 0.2s ease;
}

.item:hover {
    transform: translateY(-2px);
}

.img_grid {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #f5f5f5;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="%23f0f0f0"/><text x="50" y="50" text-anchor="middle" dy=".3em" fill="%23999" font-size="12">Caricamento...</text></svg>');
    background-repeat: no-repeat;
    background-position: center;
    background-size: 60px 60px;
}

.img_grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.img_grid img.loaded {
    opacity: 1;
}

.txt_grid {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 15px;
    flex: 1;
    height: 220px;
}

.item_txt {
    flex: 1;
    overflow: hidden;
}

.item_txt h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 2.6em;
}

.price {
    font-size: 18px;
    font-weight: bold;
    color: #e74c3c;
    margin: 5px 0;
}

.category, .color-info, .size-info {
    font-size: 12px;
    color: #666;
    margin: 2px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item_actions {
    margin-top: 10px;
}

.add-to-cart-btn {
    width: 100%;
    padding: 12px;
    background: #000;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.add-to-cart-btn:hover {
    background: #333;
}

.add-to-cart-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.source-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.source-badge.internal {
    background: #2ed573;
    color: white;
}

.source-badge.external {
    background: #3742fa;
    color: white;
}

.loading-indicator {
    text-align: center;
    padding: 60px 20px;
    font-size: 16px;
    color: #666;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-results, .error-message {
    text-align: center;
    padding: 80px 20px;
    color: #666;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.view-details-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background-color 0.3s ease;
    margin-bottom: 8px;
    width: 100%;
}

.view-details-btn:hover {
    background: #0056b3;
}

.item_actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.add-to-cart-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: background-color 0.3s ease;
    width: 100%;
}

.add-to-cart-btn:hover {
    background: #1e7e34;
}


.item_txt h3:hover {
    color: #0056b3 !important;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .view-content {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        padding: 15px;
    }

    .more-view {
        width: 90%;
        max-width: 300px;
        margin-top: 20px;
    }
    
    .item {
        height: 380px;
    }
    
    .txt_grid {
        height: 180px;
        padding: 12px;
    }
    
    .img_grid {
        height: 180px;
    }
}

@media (max-width: 480px) {
    .view-content {
        grid-template-columns: 1fr;
        padding: 10px;
    }
    
    .item {
        height: 360px;
    }
}
    </style>
</main>

<?php include 'footer.php'; ?>