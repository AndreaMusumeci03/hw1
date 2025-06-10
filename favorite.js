const favoriteCache = new Map();
const processingButtons = new Set();

function addToFavorites(productId) {
    console.log('Aggiungendo ai preferiti prodotto:', productId);
    
    return fetch('favorite.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`,
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Risposta aggiunta preferiti:', data);
        if (data.success) {
            favoriteCache.set(productId, true);
            updateAllFavoriteButtons(productId, true);
            showNotification('Aggiunto ai preferiti!', 'success');
            return true;
        } else {
            showNotification(data.error || 'Errore durante l\'aggiunta', 'error');
            return false;
        }
    })
    .catch(error => {
        console.error('Errore aggiunta preferiti:', error);
        showNotification('Errore di connessione', 'error');
        return false;
    });
}

function removeFromFavorites(productId) {
    console.log('Rimuovendo dai preferiti prodotto:', productId);
    
    return fetch('favorite.php?action=remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`,
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Risposta rimozione preferiti:', data);
        if (data.success) {
            favoriteCache.set(productId, false);
            updateAllFavoriteButtons(productId, false);
            showNotification('Rimosso dai preferiti', 'success');

            if (typeof loadFavorites === 'function') {
                setTimeout(() => {
                    loadFavorites();
                }, 1000);
            }
            return true;
        } else {
            showNotification(data.error || 'Errore durante la rimozione', 'error');
            return false;
        }
    })
    .catch(error => {
        console.error('Errore rimozione preferiti:', error);
        showNotification('Errore di connessione: ' + error.message, 'error');
        return false;
    });
}

function updateFavoriteButton(button, isFavorite) {
    if (!button) {
        console.error('Button is null or undefined');
        return;
    }
    
    console.log('Aggiornando pulsante preferiti:', isFavorite);
    
    if (isFavorite) {
        button.classList.add('is-favorite');
        button.innerHTML = '❤️';
        button.setAttribute('title', 'Rimuovi dai preferiti');
    } else {
        button.classList.remove('is-favorite');
        button.innerHTML = '🤍';
        button.setAttribute('title', 'Aggiungi ai preferiti');
    }
}

function updateAllFavoriteButtons(productId, isFavorite) {
    console.log('Aggiornando tutti i pulsanti per prodotto:', productId, 'isFavorite:', isFavorite);
    
    const buttons = document.querySelectorAll(`[data-product-id="${productId}"].favorite-btn`);
    console.log('Trovati', buttons.length, 'pulsanti da aggiornare');
    
    buttons.forEach(button => {
        updateFavoriteButton(button, isFavorite);
    });
}

function toggleFavorite(productId, button) {
    console.log('Toggle favorite chiamato con productId:', productId);
    
    if (!productId || productId === 'undefined' || productId === 'null') {
        console.error('ProductId non valido:', productId);
        showNotification('Errore: ID prodotto non valido', 'error');
        return;
    }
    
    if (!button) {
        console.error('Button non valido:', button);
        showNotification('Errore: pulsante non valido', 'error');
        return;
    }

    const buttonKey = `${productId}-${button.getBoundingClientRect().x}-${button.getBoundingClientRect().y}`;
    if (processingButtons.has(buttonKey)) {
        console.log('Pulsante già in elaborazione, ignorando click');
        return;
    }

    processingButtons.add(buttonKey);
    
    button.disabled = true;
    const originalContent = button.innerHTML;
    button.innerHTML = '⏳';
    
    let isCurrentlyFavorite = favoriteCache.get(productId);
    
    if (isCurrentlyFavorite === undefined) {
        console.log('Stato non in cache, controllando dal server...');
        
        fetch(`favorite.php?action=check&product_id=${productId}`, {
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            isCurrentlyFavorite = data.is_favorite;
            favoriteCache.set(productId, isCurrentlyFavorite);
            
            console.log('Stato attuale:', isCurrentlyFavorite ? 'preferito' : 'non preferito');
            
            // Procedi con il toggle
            if (isCurrentlyFavorite) {
                return removeFromFavorites(productId);
            } else {
                return addToFavorites(productId);
            }
        })
        .then(success => {
            if (!success && button.innerHTML === '⏳') {
                button.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Errore in toggleFavorite:', error);
            showNotification('Errore di connessione: ' + error.message, 'error');
            
            if (button.innerHTML === '⏳') {
                button.innerHTML = button.classList.contains('is-favorite') ? '❤️' : '🤍';
            }
        })
        .finally(() => {
            button.disabled = false;
            processingButtons.delete(buttonKey);
        });
    } else {
        // Se lo stato è già in cache, procedi direttamente
        console.log('Stato attuale:', isCurrentlyFavorite ? 'preferito' : 'non preferito');
        
        let favoritePromise;
        if (isCurrentlyFavorite) {
            favoritePromise = removeFromFavorites(productId);
        } else {
            favoritePromise = addToFavorites(productId);
        }
        
        favoritePromise
        .then(success => {
            if (!success && button.innerHTML === '⏳') {
                button.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Errore in toggleFavorite:', error);
            showNotification('Errore di connessione: ' + error.message, 'error');
            
            if (button.innerHTML === '⏳') {
                button.innerHTML = button.classList.contains('is-favorite') ? '❤️' : '🤍';
            }
        })
        .finally(() => {
            button.disabled = false;
            processingButtons.delete(buttonKey);
        });
    }
}

function checkFavoriteStatus(productId, button) {
    console.log('Controllo stato preferito per prodotto:', productId);
    
    if (!productId || !button) {
        console.error('Parametri non validi per checkFavoriteStatus:', { productId, button });
        return;
    }
    
    const cachedState = favoriteCache.get(productId);
    if (cachedState !== undefined) {
        console.log('Stato trovato in cache:', cachedState);
        updateFavoriteButton(button, cachedState);
        return;
    }
    
    fetch(`favorite.php?action=check&product_id=${productId}`, {
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Stato preferito ricevuto per prodotto', productId, ':', data);
        favoriteCache.set(productId, data.is_favorite);
        updateFavoriteButton(button, data.is_favorite);
    })
    .catch(error => {
        console.error('Errore nel controllo preferiti per prodotto', productId, ':', error);
        updateFavoriteButton(button, false);
    });
}

function handleFavoriteClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const button = event.currentTarget;
    const productId = button.dataset.productId;
    
    console.log('Click rilevato su pulsante con productId:', productId);
    
    if (productId && productId !== 'undefined' && productId !== 'null') {
        toggleFavorite(productId, button);
    } else {
        console.error('ProductId non valido nel click handler:', productId);
        showNotification('Errore: ID prodotto non valido', 'error');
    }
}

function initializeFavoriteButtons() {
    console.log('Inizializzazione pulsanti preferiti...');
    
    const favoriteButtons = document.querySelectorAll('.favorite-btn:not([data-favorite-initialized])');
    console.log('Trovati', favoriteButtons.length, 'pulsanti preferiti da inizializzare');
    
    favoriteButtons.forEach((button, index) => {
        const productId = button.dataset.productId;
        
        console.log(`Inizializzando pulsante ${index + 1}: productId =`, productId);
        
        if (productId && productId !== 'undefined' && productId !== 'null') {
            button.dataset.favoriteInitialized = 'true';
            
            button.addEventListener('click', handleFavoriteClick);
            
            checkFavoriteStatus(productId, button);
        } else {
            console.warn('ProductId non valido per pulsante:', productId);
        }
    });
}

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

if (!document.getElementById('favorite-styles')) {
    const style = document.createElement('style');
    style.id = 'favorite-styles';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .favorite-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 6px 12px;
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 16px;
            font-weight: 600;
            z-index: 2;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            min-width: 40px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .favorite-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .favorite-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .favorite-btn.is-favorite {
            background-color: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }
        
        .favorite-btn.is-favorite:hover {
            background-color: #c0392b;
        }
    `;
    document.head.appendChild(style);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}


const debouncedInitialize = debounce(initializeFavoriteButtons, 200);

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM caricato, inizializzando preferiti...');
    setTimeout(initializeFavoriteButtons, 100);
});


function observeProductChanges() {
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        const observer = new MutationObserver(function(mutations) {
            let shouldReinitialize = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE && 
                            (node.classList.contains('item') || node.querySelector('.item'))) {
                            shouldReinitialize = true;
                        }
                    });
                }
            });
            
            if (shouldReinitialize) {
                console.log('Nuovi prodotti rilevati, reinizializzando preferiti...');
                debouncedInitialize();
            }
        });
        
        observer.observe(searchResults, {
            childList: true,
            subtree: true
        });
        
        console.log('Observer configurato per searchResults');
    }
}


window.addEventListener('load', function() {
    observeProductChanges();
});


window.toggleFavorite = toggleFavorite;
window.addToFavorites = addToFavorites;
window.removeFromFavorites = removeFromFavorites;
window.updateFavoriteButton = updateFavoriteButton;
window.initializeFavoriteButtons = initializeFavoriteButtons;