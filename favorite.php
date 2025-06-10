<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Log per debug
error_log("Favorite.php chiamato - Action: " . ($_GET['action'] ?? 'none'));
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

if (!isset($_SESSION['user_id'])) {
    error_log("Utente non autenticato");
    echo json_encode([
        'error' => 'Utente non autenticato'
    ]);
    exit;
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'thenorthface_db';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    error_log("DB connection failed: " . $mysqli->connect_error);
    echo json_encode(['error' => 'DB connection failed: ' . $mysqli->connect_error]);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

error_log("Processing action: $action for user: $user_id");

switch ($action) {
    case 'add':
        $product_id = intval($_POST['product_id'] ?? 0);
        error_log("Add favorite - Product ID: $product_id");
        
        if ($product_id <= 0) {
            echo json_encode(['error' => 'ID prodotto non valido']);
            exit;
        }
        
        // Verifica che il prodotto esista (sia nella tabella products che come prodotto esterno)
        $check = $mysqli->prepare("SELECT id FROM products WHERE id = ?");
        $check->bind_param("i", $product_id);
        $check->execute();
        $result = $check->get_result();
        
        // Se il prodotto non esiste nella tabella products, potrebbe essere un prodotto esterno
        // In questo caso, creiamo una entry fittizia o gestiamo diversamente
        if ($result->num_rows === 0) {
            error_log("Prodotto $product_id non trovato nella tabella products");
            // Per ora, permettiamo comunque l'aggiunta ai preferiti anche per prodotti esterni
            // echo json_encode(['error' => 'Prodotto non esistente']);
            // exit;
        }
        
        // Controlla se già nei preferiti
        $stmt = $mysqli->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $existing = $stmt->get_result();
        
        if ($existing->num_rows > 0) {
            error_log("Prodotto $product_id già nei preferiti per utente $user_id");
            echo json_encode(['error' => 'Prodotto già nei preferiti']);
            exit;
        }
        
        // Aggiungi ai preferiti
        $insert = $mysqli->prepare("INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $insert->bind_param("ii", $user_id, $product_id);
        $insert->execute();
        
        if ($insert->affected_rows === 0) {
            error_log("Inserimento fallito per prodotto $product_id");
            echo json_encode(['error' => 'Inserimento fallito']);
            exit;
        }
        
        error_log("Prodotto $product_id aggiunto ai preferiti con successo");
        echo json_encode(['success' => true, 'message' => 'Aggiunto ai preferiti']);
        break;
        
    case 'remove':
        $product_id = intval($_POST['product_id'] ?? 0);
        error_log("Remove favorite - Product ID: $product_id");
        
        if ($product_id <= 0) {
            echo json_encode(['error' => 'ID prodotto non valido']);
            exit;
        }
        
        $stmt = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        
        error_log("Righe eliminate: " . $stmt->affected_rows);
        echo json_encode(['success' => true, 'message' => 'Rimosso dai preferiti']);
        break;
        
    case 'check':
        $product_id = intval($_GET['product_id'] ?? 0);
        error_log("Check favorite - Product ID: $product_id");
        
        if ($product_id <= 0) {
            error_log("Product ID non valido: $product_id");
            echo json_encode(['is_favorite' => false]);
            exit;
        }
        
        $stmt = $mysqli->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $is_favorite = $result->num_rows > 0;
        error_log("Is favorite result: " . ($is_favorite ? 'true' : 'false'));
        
        echo json_encode(['is_favorite' => $is_favorite]);
        break;
        
    case 'get':
    default:
        error_log("Get favorites for user: $user_id");
        
        // Ottieni tutti i preferiti con i dettagli del prodotto
        $stmt = $mysqli->prepare("
            SELECT f.*, p.name, p.price, p.image_url, p.description 
            FROM favorites f
            LEFT JOIN products p ON f.product_id = p.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['price']) {
                $row['price'] = (float)$row['price'];
            }
            $items[] = $row;
        }
        
        error_log("Found " . count($items) . " favorites");
        echo json_encode([
            'items' => $items,
            'count' => count($items)
        ]);
        break;
}

$mysqli->close();
?>