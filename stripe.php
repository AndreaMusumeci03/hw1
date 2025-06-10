<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);


$stripeSecret = '';

session_start();

error_log("Stripe - Session ID: " . session_id());
error_log("Stripe - Session data: " . print_r($_SESSION, true));


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

$user_id = $_SESSION['user_id'];

try {
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
    $total = 0.0;
    $count = 0;

    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float)$row['price'];
        $row['quantity'] = (int)$row['quantity'];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $count += $row['quantity'];
        $items[] = $row;
    }

    if (empty($items)) {
        echo json_encode(['error' => 'Carrello vuoto']);
        exit;
    }

    error_log("Stripe - Carrello trovato: " . count($items) . " items, totale: " . $total);

    $line_items = [];
    foreach ($items as $i => $item) {
        $line_items["line_items[{$i}][price_data][currency]"] = 'eur';
        $line_items["line_items[{$i}][price_data][product_data][name]"] = $item['name'];
        $line_items["line_items[{$i}][price_data][unit_amount]"] = intval($item['price'] * 100);
        $line_items["line_items[{$i}][quantity]"] = $item['quantity'];
    }

} catch (Exception $e) {
    error_log("Stripe - Errore database: " . $e->getMessage());
    echo json_encode(['error' => 'Errore nel recupero del carrello: ' . $e->getMessage()]);
    exit;
} finally {
    $mysqli->close();
}

$params = array_merge($line_items, [
    'mode' => 'payment',
    'success_url' => 'https://localhost/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'https://localhost/cancel.php',
    'payment_method_types[0]' => 'card'
]);

error_log("Stripe - Line items: " . print_r($line_items, true));

$postFields = http_build_query($params);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_USERPWD, $stripeSecret . ':');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

error_log("Stripe - HTTP Code: " . $httpcode);
error_log("Stripe - Response: " . $response);

curl_close($ch);

if ($httpcode === 200) {
    $data = json_decode($response, true);
    echo json_encode(['url' => $data['url']]);
} else {
    $data = json_decode($response, true);
    $error = isset($data['error']['message']) ? $data['error']['message'] : 'Errore con Stripe';
    error_log("Stripe - Errore: " . $error);
    http_response_code(500);
    echo json_encode(['error' => $error]);
}
?>