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
</head>
<body id="body">
<?php
    $html_started = true;
}
?>
        <div class="separa"></div>
    <script src="https://static.elfsight.com/platform/platform.js" async></script>
    <div class="elfsight-app-716d4544-a4f0-4d1c-bd18-b1df56150e46" data-elfsight-app-lazy></div>
    <div class="white"></div>
    <div class="separa"></div>
    <footer>
        <div>
            <section>
                <div class="Iscriviti">
                    <h2>
                        Unisciti alla nostra community
                    </h2>
                    <p>
                        Iscriviti alla newsletter per ricevere offerte esclusive e aggiornamenti sui nuovi arrivi.
                    </p>
                    <button>
                        <span>Iscriviti ora</span>
                    </button>
                </div>
            </section>
            <div id="sub-container">
                <div class="sub-items">
                    <h2>Acquista</h2>
                    <ul>
                        <li>Uomo</li>
                        <li>Donna</li>
                        <li>Bambini</li>
                        <li>Borse & attrezzature</li>
                        <li> Scarpe</li> 
                    </ul>
                </div>
                <div class="sub-items">
                    <h2>Ordini</h2>
                    <ul>
                        <li>Segui il tuo ordine</li>
                        <li>Spedizioni</li>
                        <li>Resi</li>
                        <li>Sconto Studenti</li>
                    </ul>
                </div>
                <div class="sub-items">
                    <h2>Help</h2>
                    <ul>
                        <li>Contattaci</li>
                        <li>Domande Frequenti</li>
                        <li>Garanzia</li>
                        <li>Condizioni d'uso</li>
                        <li>Informazioni sulla Privacy</li>
                        <li>Guida Alle Taglie</li>
                        <li>Preferenze dei Coockie</li>
                        <li>Dichiarazione di Conformità</li>
                    </ul>
                </div>
                <div class="sub-items">
                    <h2>Chi siamo</h2>
                    <ul>
                        <li>La Nostra Storia</li>
                        <li>Sostenibilità</li>
                        <li>Atleti</li>
                        <li>Tecnologie</li>
                        <li>The North Face Pro Programme</li>
                        <li>Lavora Con Noi</li>
                        <li>Notizie</li>
                    </ul>
                </div>
                <div class="sub-items">
                    <h2>Gli Eventi</h2>
                    <ul>
                        <li>Basecamp</li>
                        <li>Transgrancanaria</li>
                    </ul>
                </div>
            </div>

            <div id="sub-container2">
                <div class="sub-items2"></div>
                <div class="sub-items2"></div>
                <div class="sub-items2"></div>
            </div>

            
        </div>

    </footer>