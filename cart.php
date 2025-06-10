<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();


error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => 'Utente non autenticato',
        'session_status' => session_status(),
        'session' => $_SESSION
    ]);
    exit;
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'thenorthface_db';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'DB connection failed: ' . $mysqli->connect_error]);
    exit;
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];


error_log("Action: $action, User ID: $user_id");

switch ($action) {
    case 'add':
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        error_log("Adding product - PID: $product_id, QTY: $quantity");
        

        $check = $mysqli->prepare("SELECT id FROM products WHERE id = ?");
        $check->bind_param("i", $product_id);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            echo json_encode(['error' => 'Prodotto non esistente']);
            exit;
        }
        

        $stmt = $mysqli->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $new_qty = $item['quantity'] + $quantity;
            $update = $mysqli->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $item['id']);
            $update->execute();
            
            if ($update->affected_rows === 0) {
                echo json_encode(['error' => 'Aggiornamento fallito']);
                exit;
            }
        } else {
            $insert = $mysqli->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $user_id, $product_id, $quantity);
            $insert->execute();
            
            if ($insert->affected_rows === 0) {
                echo json_encode(['error' => 'Inserimento fallito']);
                exit;
            }
        }
        
        echo json_encode(['success' => true, 'debug' => ['product_id' => $product_id, 'user_id' => $user_id]]);
        break;
        
    case 'remove':
        $product_id = $_POST['product_id'] ?? 0;
        
        $stmt = $mysqli->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
        break;
        
    case 'update':
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if ($quantity <= 0) {

            $stmt = $mysqli->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        } else {

            $stmt = $mysqli->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
        }
        
        echo json_encode(['success' => true]);
        break;
        
case 'get':
default:

    $stmt = $mysqli->prepare("
        SELECT ci.*, p.name, p.price, p.image_url 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    
    while ($row = $result->fetch_assoc()) {

        $row['price'] = (float)$row['price'];
        $items[] = $row;
    }
    

    $total = 0;
    foreach ($items as &$item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
    }
    
    echo json_encode([
        'items' => $items,
        'total' => $total,
        'count' => count($items)
    ]);
    break;
}


$mysqli->close();
?>