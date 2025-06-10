<?php
session_start();
header("Content-Type: application/json");
error_reporting(E_ALL); 
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if (empty($action)) {
    $response['message'] = 'Azione non valida';
    echo json_encode($response);
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "thenorthface_db");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Connessione al database fallita']));
}

//Controllo disponibilità username
if ($action === 'check_username') {
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        echo json_encode(['available' => false, 'message' => 'Username vuoto']);
        exit;
    }
    
    // Validazione email
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['available' => false, 'message' => 'Email non valida']);
        exit;
    }
    
    // Controlla se l'username esiste già
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $available = mysqli_num_rows($result) === 0;
    
    echo json_encode([
        'available' => $available,
        'message' => $available ? 'Username disponibile' : 'Username già registrato'
    ]);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// AZIONE: Controllo stato login
if ($action === 'check_status') {
    $logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    echo json_encode([
        'logged_in' => $logged_in,
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null
    ]);
    exit;
}

// LOGOUT
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout effettuato']);
    exit;
}

// LOGIN
if ($action === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $response['message'] = 'Username e password obbligatori';
        echo json_encode($response);
        exit;
    }

    // Query con prepared statement (versione procedurale)
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) === 1) {
        $user = mysqli_fetch_assoc($res);
        
        // Verifica password hashata
        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["username"] = $user['username'];
            
            $response['success'] = true;
            $response['message'] = 'Login effettuato!';
            $response['redirect'] = 'index.php';
        } else {
            $response['message'] = 'Password errata';
        }
    } else {
        $response['message'] = 'Utente non trovato';
    }

} elseif ($action === 'register') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validazione email
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email non valida';
        echo json_encode($response);
        exit;
    }

    // Validazione password avanzata lato server
    if (strlen($password) < 8) {
        $response['message'] = 'Password troppo corta (min 8 caratteri)';
        echo json_encode($response);
        exit;
    }
    
    // Controlli aggiuntivi per sicurezza password
    if (!preg_match('/[A-Z]/', $password)) {
        $response['message'] = 'Password deve contenere almeno una lettera maiuscola';
        echo json_encode($response);
        exit;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $response['message'] = 'Password deve contenere almeno una lettera minuscola';
        echo json_encode($response);
        exit;
    }
    
    if (!preg_match('/\d/', $password)) {
        $response['message'] = 'Password deve contenere almeno un numero';
        echo json_encode($response);
        exit;
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $response['message'] = 'Password deve contenere almeno un carattere speciale';
        echo json_encode($response);
        exit;
    }

    $check_stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE username = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $response['message'] = 'Email già registrata';
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($insert_stmt, "ss", $username, $hashed_password);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $_SESSION["user_id"] = mysqli_insert_id($conn);
        $_SESSION["username"] = $username;
        
        $response['success'] = true;
        $response['message'] = 'Registrazione completata!';
        $response['redirect'] = 'index.php';
    } else {
        $response['message'] = 'Errore database: ' . mysqli_error($conn);
    }
    
    mysqli_stmt_close($insert_stmt);
    mysqli_stmt_close($check_stmt);
}

mysqli_close($conn);
echo json_encode($response);
?>