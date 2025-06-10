<?php
// header.php - Solo la sezione header (da includere nelle pagine)

// Parametri opzionali per personalizzare il comportamento
$page_title = isset($page_title) ? $page_title : 'The North Face';
$current_page = isset($current_page) ? $current_page : '';


// Se non è già stato incluso il DOCTYPE, lo includiamo
if (!isset($html_started)) {
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gravitas+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="mhw3.css">
    <script src="mhw3.js" defer></script>
    <script src="user.js" defer></script>
    <script src="cart.js" defer></script>
</head>
<body id="body">
<?php
    $html_started = true;
}
?>
    <header id="main">
        <div class="nav-container">
            <div class="bottone-mobile">
                <div class="menu-itemMobile" id="menuMobile">
                    <button><img src="https://img.icons8.com/?size=100&id=NvAez9zuZg1W&format=png&color=000000" alt="Menu Mobile"></button>
                    
                    <ul class="dropdown-mobile" id="dropdownMenuMobile">
                        <li><a href="#">Accedi</a></li>
                        <li><a href="#">Uomo</a></li>
                        <li><a href="#">Donna</a></li>
                        <li><a href="#">Bambino</a></li>
                        <li><a href="#">Scarpe</a></li>
                        <li><a href="#">Accessori</a></li>
                    </ul>
                </div>
            </div> 

            <button id="home"><img src="https://www.thenorthface.it/img/logos/thenorthface/default.svg" alt="The North Face Logo" ></button>
           
            <!-- Menu Uomo -->
            <div class="menu-item" id="menuItem">
                <button class="div-button"><span>Uomo</span></button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="topmenu"><a href="#">Vedi tutto Uomo ></a></div>
                    <div class="section-menu">
                        <div class="section-item">
                            <h4>Novità e tendenze</h4>
                            <ul>
                                <li>Nuovi Arrivi</li>
                                <li>Best Sellers</li>
                                <li>Ultime collezioni</li>
                            </ul>
                            <h4>Bundles</h4>
                        </div>
                        <div class="section-item">
                            <h4>Giacche</h4>
                            <ul>
                                <li>Impermeabile</li>
                                <li>Giacche leggere</li>
                                <li>Gilet</li>
                                <li>Tutte le giacche imbottite</li>
                                <li>Giacche Nuptse & piumini</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Top</h4>
                            <ul>
                                <li>T-shirt & camicie</li>
                                <li>T-shirt tecniche</li>
                                <li>Felpe & felpe con cappuccio</li>
                                <li>Pile & strati intermedi</li>
                            </ul>
                            <h4>Pantaloni</h4>
                            <ul>
                                <li>Pantaloncini</li>
                                <li>Pantaloni</li>
                                <li>Pantaloni sportivi e da tuta</li>
                                <li>Leggins</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Scarpe</h4>
                            <ul>
                                <li>Scarpe da trail running</li>
                                <li>Scarpe da trekking</li>
                                <li>Scarpe casual</li>
                                <li>Ciabatte, Sandali & Mules</li>
                            </ul>
                            <h4>Accessori</h4>
                            <ul>
                                <li>Zaini</li>
                                <li>Cappellini e Cappelli</li>
                                <li>Berretti</li>
                                <li>Calze</li>
                                <li>Sciarpe e guanti</li>
                                <li>Accessori</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Attività</h4>
                            <ul>
                                <li>Sci & snowboard</li>
                                <li>Sci alpinismo</li>
                                <li>Alpinismo</li>
                                <li>Trail running</li>
                                <li>Allenamento</li>
                            </ul>
                            <h4>Collezioni</h4>
                            <ul>
                                <li>Summit series</li>
                                <li>In esclusiva</li>
                                <li>Sustainable gear</li>
                            </ul>
                            <h4>Outlet</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Donna -->
            <div class="menu-item" id="menuItemDonna">
                <button class="div-button"><span>Donna</span></button>
                <div class="dropdown-menu" id="dropdownMenuDonna">
                    <div class="topmenu"><a href="#">Vedi tutto Donna ></a></div>
                    <div class="section-menu">
                        <div class="section-item">
                            <h4>Novità e tendenze</h4>
                            <ul>
                                <li>Nuovi Arrivi</li>
                                <li>Best Sellers</li>
                                <li>Ultime collezioni</li>
                            </ul>
                            <h4>Bundles</h4>
                        </div>
                        <div class="section-item">
                            <h4>Giacche</h4>
                            <ul>
                                <li>Impermeabile</li>
                                <li>Giacche leggere</li>
                                <li>Gilet</li>
                                <li>Tutte le giacche imbottite</li>
                                <li>Giacche Nuptse & piumini</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Top</h4>
                            <ul>
                                <li>T-shirt & camicie</li>
                                <li>T-shirt tecniche</li>
                                <li>Felpe & felpe con cappuccio</li>
                                <li>Pile & strati intermedi</li>
                            </ul>
                            <h4>Pantaloni</h4>
                            <ul>
                                <li>Pantaloncini</li>
                                <li>Pantaloni</li>
                                <li>Pantaloni sportivi e da tuta</li>
                                <li>Leggins</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Scarpe</h4>
                            <ul>
                                <li>Scarpe da trail running</li>
                                <li>Scarpe da trekking</li>
                                <li>Scarpe casual</li>
                                <li>Ciabatte, Sandali & Mules</li>
                            </ul>
                            <h4>Accessori</h4>
                            <ul>
                                <li>Zaini</li>
                                <li>Cappellini e Cappelli</li>
                                <li>Berretti</li>
                                <li>Calze</li>
                                <li>Sciarpe e guanti</li>
                                <li>Accessori</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Attività</h4>
                            <ul>
                                <li>Sci & snowboard</li>
                                <li>Sci alpinismo</li>
                                <li>Alpinismo</li>
                                <li>Trail running</li>
                                <li>Allenamento</li>
                            </ul>
                            <h4>Collezioni</h4>
                            <ul>
                                <li>Summit series</li>
                                <li>In esclusiva</li>
                                <li>Sustainable gear</li>
                            </ul>
                            <h4>Outlet</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Bambino -->
            <div class="menu-item" id="menuItemBamb">
                <button class="div-button"><span>Bambino</span></button>
                <div class="dropdown-menu" id="dropdownMenuBamb">
                    <div class="topmenu"><a href="#">Vedi tutto Bambino ></a></div>
                    <div class="section-menu">
                        <div class="section-item">
                            <h4>Novità e tendenze</h4>
                            <ul>
                                <li>Nuovi Arrivi</li>
                                <li>Best Sellers</li>
                                <li>Mini me</li>
                            </ul>
                            <h4>Bundles</h4>
                        </div>
                        <div class="section-item">
                            <h4>Ragazzo (7-16 anni)</h4>
                            <ul>
                                <li>Cappotti & giacche</li>
                                <li>Giacche leggere</li>
                                <li>Gilet</li>
                                <li>Pile & felpe</li>
                                <li>T-shirt</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Ragazza (7-16 anni)</h4>
                            <ul>
                                <li>T-shirt & camicie</li>
                                <li>T-shirt tecniche</li>
                                <li>Felpe & felpe con cappuccio</li>
                                <li>Pile & strati intermedi</li>
                            </ul>
                            <h4>Pantaloni</h4>
                            <ul>
                                <li>Pantaloncini</li>
                                <li>Pantaloni</li>
                                <li>Pantaloni sportivi e da tuta</li>
                                <li>Leggins</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Bambini piccoli & neonati</h4>
                            <ul>
                                <li>Neonato (0-2 anni)</li>
                                <li>Bambini piccoli (2-7 anni)</li>
                            </ul>
                            <h4>Scarpe & accessori</h4>
                            <ul>
                                <li>Zaini</li>
                                <li>Cappellini & guanti</li>
                                <li>Scarpe</li>
                            </ul>
                        </div>
                        <div class="section-item">
                            <h4>Attività</h4>
                            <ul>
                                <li>Trekking</li>
                                <li>Running & Allenamento</li>
                                <li>Sci & snowboard</li>
                            </ul>
                            <h4>Outlet bambino</h4>
                        </div>
                    </div>
                </div>
            </div>

            <button class="div-button"><span>Scarpe</span></button>                
            <button class="div-button"><span>Borse</span></button>
            <button class="div-button"><span>Outlet</span></button>
            <button class="div-button"><span>Esplora</span></button>

            <!-- Barra di ricerca -->
            <div class="search-bar">
                <form class="search">
                    <input type="text" placeholder="Cerca..." name="search">
                    <button type="submit">
                        <img src="https://img.icons8.com/?size=100&id=7695&format=png&color=000000" alt="Cerca">
                    </button>
                </form>
            </div>

            <!-- Login -->
            <div class="login" id="login1">
                <button class="profile">
                    <img src="https://img.icons8.com/?size=100&id=ABBSjQJK83zf&format=png&color=000000" alt="Profilo utente">
                </button>
                <ul class="dropdown-login" id="dropdownLogin">
                    <div class="tabs">
                        <button id="loginTab" class="active">Login</button>
                        <button id="registerTab">Registrati</button>
                    </div>
                    <form id="authForm" method="POST" action="<?php echo $login_action; ?>">
                        <label for="username">Username (Email):</label>
                        <input type="text" id="username" name="username" required>
                  
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                        
                        <label><input type="checkbox" name="remember"> Ricordami</label>
                        <input type="hidden" id="actionType" name="action" value="login">

                        <button type="submit" id="authSubmit">Accedi</button>
                    </form>
                    <p id="authMessage"></p>
                </ul>
            </div>
        </div>

        <!-- Carrello -->
        <div class="carrello" id="carrello">
            <button class="cart" id="cartToggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="black">
                    <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm11.8-2.4c-.3-.4-.7-.6-1.2-.6H8.1l-.9-2H18c.8 0 1.5-.5 1.8-1.2l2.7-6c.2-.5.2-1.1-.1-1.6-.3-.5-.9-.8-1.4-.8H5.2l-.9-2H1v2h2l3.6 7.6-1.4 2.4C4.5 15.8 5.2 17 6.2 17h12c.6 0 1-.4 1-1 0-.2-.1-.5-.2-.6zM17 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span class="cart-count">0</span> 
            </button>
            <div class="right-showcontainer" id="rightShowContainer">
                <div class="titolo"><span>Il tuo carrello</span></div>
                <div class="cart-content" id="cartContent">
                    <!-- Contenuto dinamico -->
                </div>
                <div class="total-price">
                    <span>Totale:</span>
                    <span id="total-price">€ 0.00</span>
                    <button id="checkout-btn" class="nav-check">Procedi al Checkout</button>
                </div>
            </div>
        </div>
    </header>


    <style>
    header{
        z-index: 1000;
        position: relative; /* Necessario per z-index */
    }
    

    
    </style>


<script>

    document.addEventListener('DOMContentLoaded',function(){
        const home = document.getElementById('home');
        const gocheck= document.getElementById('checkout-btn');
        if(home){
            home.addEventListener('click', function() {
                window.location.href = 'index.php'; // Reindirizza alla homepage
            });
        }
        

        if(gocheck){
            gocheck.addEventListener('click', function() {
                window.location.href = 'checkout.php'; // Reindirizza alla pagina di checkout
            });
        }
        
    });
    

    document.addEventListener('DOMContentLoaded', function() {
    // Selettori multipli per trovare il campo di ricerca nella navbar
    const navSearchInput = document.querySelector('input[type="search"], input[placeholder*="cerca"], input[placeholder*="search"], .navbar input, header input, nav input, #search, #navSearch, .search-field, .search-input');
    const navSearchBtn = document.querySelector('.search-btn, .btn-search, button[type="submit"], .navbar button, header button, nav button, #searchBtn, #navSearchBtn');
    const navSearchForm = document.querySelector('form.search, .search-form, .navbar-search-form, #navSearchForm, form:has(input[type="search"]), form:has(input[placeholder*="cerca"])');
    
    // Funzione per reindirizzare alla pagina di ricerca
    function redirectToSearch(query) {
        const trimmedQuery = query.trim();
        if (trimmedQuery) {
            // Reindirizza alla pagina search.php con il parametro di ricerca
            console.log('Reindirizzamento a:', `search.php?q=${encodeURIComponent(trimmedQuery)}`);
            window.location.href = `search.php?q=${encodeURIComponent(trimmedQuery)}`;
        } else {
            // Se la query è vuota, vai comunque alla pagina di ricerca
            window.location.href = 'search.php';
        }
    }
    
    // DEBUG: Verifica se gli elementi sono stati trovati
    console.log('Elementi trovati:');
    console.log('Input:', navSearchInput);
    console.log('Button:', navSearchBtn);
    console.log('Form:', navSearchForm);
    
    // Event listener per il pulsante di ricerca - VERSIONE SEMPLIFICATA
    if (navSearchBtn) {
        console.log('Aggiungendo event listener al pulsante');
        navSearchBtn.addEventListener('click', function(e) {
            console.log('Click pulsante ricerca');
            e.preventDefault();
            e.stopPropagation();
            
            const query = navSearchInput ? navSearchInput.value : '';
            console.log('Query dal pulsante:', query);
            redirectToSearch(query);
        });
    } else {
        console.log('Pulsante di ricerca non trovato, provo a cercarlo in modo diverso...');
        
        // Fallback: cerca tutti i pulsanti vicini a campi di input
        const allInputs = document.querySelectorAll('input');
        allInputs.forEach(input => {
            const nextButton = input.nextElementSibling;
            const parentButton = input.parentElement.querySelector('button');
            
            if (nextButton && nextButton.tagName === 'BUTTON') {
                console.log('Trovato pulsante adiacente, aggiungendo listener');
                nextButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    redirectToSearch(input.value);
                });
            }
            
            if (parentButton) {
                console.log('Trovato pulsante nel parent, aggiungendo listener');
                parentButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    redirectToSearch(input.value);
                });
            }
        });
    }
    
    // Event listener per il tasto Enter nel campo di ricerca - VERSIONE SEMPLIFICATA
    if (navSearchInput) {
        console.log('Aggiungendo event listener per Enter al campo input');
        navSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                console.log('Premuto Enter, query:', this.value);
                e.preventDefault();
                e.stopPropagation();
                redirectToSearch(this.value);
            }
        });
    } else {
        console.log('Campo input non trovato, cerco tutti gli input...');
        
        // Fallback: aggiungi listener a tutti gli input che potrebbero essere di ricerca
        const allInputs = document.querySelectorAll('input[type="text"], input[type="search"], input:not([type])');
        allInputs.forEach(input => {
            // Controlla se sembra un campo di ricerca
            const placeholder = input.placeholder?.toLowerCase() || '';
            const id = input.id?.toLowerCase() || '';
            const className = input.className?.toLowerCase() || '';
            
            if (placeholder.includes('cerca') || placeholder.includes('search') || 
                id.includes('search') || className.includes('search')) {
                
                console.log('Trovato possibile campo di ricerca:', input);
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        redirectToSearch(this.value);
                    }
                });
            }
        });
    }
    
    // Event listener per il form di ricerca - VERSIONE SEMPLIFICATA
    if (navSearchForm) {
        console.log('Form di ricerca trovato, aggiungendo listener');
        navSearchForm.addEventListener('submit', function(e) {
            console.log('Submit del form');
            e.preventDefault();
            e.stopPropagation();
            
            const formData = new FormData(this);
            const query = formData.get('q') || formData.get('search') || formData.get('query') || '';
            
            // Se non trova nei form data, cerca nell'input del form
            if (!query) {
                const formInput = this.querySelector('input[type="text"], input[type="search"], input:not([type])');
                if (formInput) {
                    redirectToSearch(formInput.value);
                    return;
                }
            }
            
            redirectToSearch(query);
        });
    } else {
        console.log('Form non trovato, cerco tutti i form...');
        
        // Fallback: cerca tutti i form che potrebbero contenere ricerca
        const allForms = document.querySelectorAll('form');
        allForms.forEach(form => {
            const hasSearchInput = form.querySelector('input[placeholder*="cerca"], input[placeholder*="search"], input[id*="search"], input[class*="search"]');
            if (hasSearchInput) {
                console.log('Trovato form con campo di ricerca');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    redirectToSearch(hasSearchInput.value);
                });
            }
        });
    }
    
    // FALLBACK UNIVERSALE: Intercetta TUTTI i click e Enter
    document.addEventListener('click', function(e) {
        // Se il click è su un pulsante con icone di ricerca o testo "cerca"
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I' || e.target.tagName === 'SVG') {
            const button = e.target.closest('button');
            if (button) {
                const buttonText = button.textContent?.toLowerCase() || '';
                const buttonHTML = button.innerHTML?.toLowerCase() || '';
                
                if (buttonText.includes('cerca') || buttonText.includes('search') || 
                    buttonHTML.includes('search') || buttonHTML.includes('magnify')) {
                    
                    console.log('Intercettato click su pulsante di ricerca');
                    e.preventDefault();
                    
                    // Cerca il campo input più vicino
                    const nearestInput = button.parentElement.querySelector('input') || 
                                       button.closest('form')?.querySelector('input') ||
                                       document.querySelector('input[type="search"], input[placeholder*="cerca"]');
                    
                    if (nearestInput) {
                        redirectToSearch(nearestInput.value);
                    } else {
                        redirectToSearch('');
                    }
                }
            }
        }
    });
    
    console.log('Setup di ricerca navbar completato');
    
    // Rimuovo le funzioni di suggerimenti per semplificare
    // Se vuoi riattivarle in futuro, basta decommentare
    
    /*
    // Funzione per ottenere suggerimenti di ricerca
    function fetchSearchSuggestions(query, inputElement) {
        // ... codice suggerimenti
    }
    */
});

    
</script>