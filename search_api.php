<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'thenorthface_db',
    'charset' => 'utf8mb4'
];

try {
    $mysqli = new mysqli(
        $db_config['host'],
        $db_config['user'],
        $db_config['pass'],
        $db_config['name']
    );
    
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset($db_config['charset']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore di connessione al database']);
    exit;
}

$query = isset($_POST['query']) ? trim($_POST['query']) : '';
$page = isset($_POST['page']) ? max(1, min(20, (int)$_POST['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

if (strlen($query) > 200) {
    echo json_encode(['success' => false, 'message' => 'Query troppo lunga']);
    exit;
}

$response = [
    'success' => true,
    'products' => [],
    'total' => 0,
    'page' => $page,
    'per_page' => $per_page,
    'has_more' => false,
    'query' => $query
];

try {
    $local_products = [];
    $total_local = 0;
    
    if (!empty($query)) {
        $count_sql = "SELECT COUNT(*) as total FROM products 
                      WHERE (name LIKE ? OR description LIKE ? OR category LIKE ? OR color LIKE ?)";
        $count_stmt = $mysqli->prepare($count_sql);
        
        if ($count_stmt) {
            $search_term = '%' . $query . '%';
            $count_stmt->bind_param('ssss', $search_term, $search_term, $search_term, $search_term);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_local = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        }
        
        $sql = "SELECT 
                    id, 
                    name, 
                    price,
                    original_price,
                    image_url as image, 
                    category,
                    color,
                    size,
                    description,
                    stock,
                    rating,
                    reviews_count,
                    'internal' as source
                FROM products 
                WHERE 
                    (name LIKE ? OR description LIKE ? OR category LIKE ? OR color LIKE ?)
                    AND stock = 1
                ORDER BY 
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN category LIKE ? THEN 2
                        WHEN color LIKE ? THEN 3
                        ELSE 4
                    END,
                    rating DESC,
                    name ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $exact_term = $query . '%';
            $stmt->bind_param('sssssssii', 
                $search_term, $search_term, $search_term, $search_term, 
                $exact_term, $exact_term, $exact_term, 
                $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $row['price'] = (float)$row['price'];
                $row['original_price'] = $row['original_price'] ? (float)$row['original_price'] : null;
                $row['rating'] = (float)$row['rating'];
                $row['reviews_count'] = (int)$row['reviews_count'];
                $row['stock'] = (bool)$row['stock'];
                $local_products[] = $row;
            }
            
            $stmt->close();
        }
        
        $external_products = [];
        if ($page === 1) {
            $remaining_slots = $per_page - count($local_products);
            if ($remaining_slots > 0) {
                $external_products = searchExternalAPI($query, min(6, $remaining_slots));
            }
        }
        
    } else {
        
        $count_result = $mysqli->query("SELECT COUNT(*) as total FROM products WHERE stock = 1");
        $total_local = $count_result ? $count_result->fetch_assoc()['total'] : 0;
        
        $sql = "SELECT 
                    id, name, price, original_price, image_url as image, category, color, size, 
                    description, stock, rating, reviews_count, 'internal' as source
                FROM products 
                WHERE stock = 1
                ORDER BY rating DESC, reviews_count DESC, name ASC
                LIMIT $per_page OFFSET $offset";
        
        $result = $mysqli->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['price'] = (float)$row['price'];
                $row['original_price'] = $row['original_price'] ? (float)$row['original_price'] : null;
                $row['rating'] = (float)$row['rating'];
                $row['reviews_count'] = (int)$row['reviews_count'];
                $row['stock'] = (bool)$row['stock'];
                $local_products[] = $row;
            }
        }
        
        $external_products = [];
        if ($page === 1) {
            $remaining_slots = $per_page - count($local_products);
            if ($remaining_slots > 0) {
                $external_products = getPopularExternalProducts(min(4, $remaining_slots));
            }
        }
    }
    
    $all_products = array_merge($local_products, $external_products);
    
    $has_more = false;
    
    if ($page === 1) {
        $has_more = $total_local > count($local_products);
    } else {
        $has_more = (count($local_products) >= $per_page) && 
                   (($offset + count($local_products)) < $total_local);
    }
    
    if (count($all_products) === 0) {
        $has_more = false;
    }
    
    if ($page >= 15) {
        $has_more = false;
    }
    
    $response['products'] = $all_products;
    $response['total'] = $total_local;
    $response['has_more'] = $has_more;
    
    if (!empty($query)) {
        logSearch($mysqli, $query, count($local_products), count($external_products));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Si è verificato un errore durante la ricerca',
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);
$mysqli->close();

function searchExternalAPI($query, $limit = 6) {
    $products = [];
    
    if (strlen($query) < 2 || $limit <= 0) {
        return $products;
    }
    
    try {
        $mock_data = [
            [
                'id' => 'ext_' . md5($query . '1'),
                'title' => 'Giacca ' . ucfirst($query) . ' Pro',
                'price' => rand(79, 199) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/4CAF50/white?text=Partner+Giacca',
                'category' => 'Giacche',
                'color' => 'Nero',
                'rating' => 4.3
            ],
            [
                'id' => 'ext_' . md5($query . '2'),
                'title' => 'Zaino ' . ucfirst($query) . ' Adventure',
                'price' => rand(89, 159) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/2196F3/white?text=Partner+Zaino',
                'category' => 'Zaini',
                'color' => 'Blu',
                'rating' => 4.6
            ],
            [
                'id' => 'ext_' . md5($query . '3'),
                'title' => 'Scarpe ' . ucfirst($query) . ' Trail',
                'price' => rand(119, 249) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/FF9800/white?text=Partner+Scarpe',
                'category' => 'Calzature',
                'color' => 'Grigio',
                'rating' => 4.4
            ],
            [
                'id' => 'ext_' . md5($query . '4'),
                'title' => 'Pantalone ' . ucfirst($query) . ' Outdoor',
                'price' => rand(69, 139) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/9C27B0/white?text=Partner+Pantaloni',
                'category' => 'Pantaloni',
                'color' => 'Verde',
                'rating' => 4.2
            ],
            [
                'id' => 'ext_' . md5($query . '5'),
                'title' => 'Felpa ' . ucfirst($query) . ' Comfort',
                'price' => rand(49, 99) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/FF5722/white?text=Partner+Felpa',
                'category' => 'Felpe',
                'color' => 'Rosso',
                'rating' => 4.5
            ],
            [
                'id' => 'ext_' . md5($query . '6'),
                'title' => 'Cappello ' . ucfirst($query) . ' Style',
                'price' => rand(29, 59) + 0.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/607D8B/white?text=Partner+Cappello',
                'category' => 'Accessori',
                'color' => 'Bianco',
                'rating' => 4.1
            ]
        ];
        
        $count = 0;
        foreach ($mock_data as $item) {
            if ($count >= $limit) break;
            
            if (stripos($item['title'], $query) !== false || 
                stripos($item['category'], $query) !== false ||
                stripos($item['color'], $query) !== false ||
                strlen($query) >= 3) {
                
                $products[] = [
                    'id' => $item['id'],
                    'name' => $item['title'],
                    'price' => (float)$item['price'],
                    'original_price' => $item['original_price'],
                    'image' => $item['image'],
                    'category' => $item['category'],
                    'color' => $item['color'],
                    'rating' => $item['rating'],
                    'reviews_count' => rand(10, 150),
                    'stock' => true,
                    'source' => 'external'
                ];
                $count++;
            }
        }
        
    } catch (Exception $e) {
        $products = [];
    }
    
    return $products;
}

function getPopularExternalProducts($limit = 4) {
    $products = [];
    
    try {
        $popular_items = [
            [
                'id' => 'pop_1',
                'name' => 'Giacca Bestseller Partner',
                'price' => 149.99,
                'original_price' => 199.99,
                'image' => 'https://via.placeholder.com/300x200/E91E63/white?text=Top+Giacca',
                'category' => 'Giacche',
                'color' => 'Nero',
                'rating' => 4.8
            ],
            [
                'id' => 'pop_2',
                'name' => 'Zaino Più Venduto Partner',
                'price' => 119.99,
                'original_price' => null,
                'image' => 'https://via.placeholder.com/300x200/00BCD4/white?text=Top+Zaino',
                'category' => 'Zaini',
                'color' => 'Blu',
                'rating' => 4.7
            ],
            [
                'id' => 'pop_3',
                'name' => 'Scarpe Top Rated Partner',
                'price' => 189.99,
                'original_price' => 229.99,
                'image' => 'https://via.placeholder.com/300x200/795548/white?text=Top+Scarpe',
                'category' => 'Calzature',
                'color' => 'Marrone',
                'rating' => 4.9
            ],
            [
                'id' => 'pop_4',
                'name' => 'Tenda Campeggio Premium',
                'price' => 299.99,
                'original_price' => 349.99,
                'image' => 'https://via.placeholder.com/300x200/607D8B/white?text=Top+Tenda',
                'category' => 'Campeggio',
                'color' => 'Verde',
                'rating' => 4.6
            ]
        ];
        
        for ($i = 0; $i < min($limit, count($popular_items)); $i++) {
            $item = $popular_items[$i];
            $products[] = [
                'id' => 'ext_pop_' . $item['id'],
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'original_price' => $item['original_price'] ? (float)$item['original_price'] : null,
                'image' => $item['image'],
                'category' => $item['category'],
                'color' => $item['color'],
                'rating' => $item['rating'],
                'reviews_count' => rand(50, 300),
                'stock' => true,
                'source' => 'external'
            ];
        }
        
    } catch (Exception $e) {
        $products = [];
    }
    
    return $products;
}

function logSearch($mysqli, $query, $local_count, $external_count) {
    try {
        $check_table = $mysqli->query("SHOW TABLES LIKE 'search_logs'");
        if ($check_table->num_rows == 0) {
            $create_table = "CREATE TABLE search_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                query VARCHAR(255) NOT NULL,
                results_count INT DEFAULT 0,
                external_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_query (query)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $mysqli->query($create_table);
        }
        
        $stmt = $mysqli->prepare("
            INSERT INTO search_logs (query, results_count, external_count, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            results_count = VALUES(results_count),
            external_count = VALUES(external_count),
            created_at = NOW()
        ");
        
        if ($stmt) {
            $stmt->bind_param('sii', $query, $local_count, $external_count);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        
    }
}
?>