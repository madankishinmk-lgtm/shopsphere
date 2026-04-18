<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$action = $input['action'] ?? '';
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);

if (!$productId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Get user's active cart
$stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmtCart->execute([$_SESSION['user_id']]);
$cart = $stmtCart->fetch();

if (!$cart) {
    // Re-create cart if deleted for some reason
    $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)")->execute([$_SESSION['user_id']]);
    $cartId = $pdo->lastInsertId();
} else {
    $cartId = $cart['id'];
}

// Fetch stock validation
$stmtProd = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmtProd->execute([$productId]);
$product = $stmtProd->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$stock = $product['stock'];

// Fetch current item in cart if exists
$stmtItem = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
$stmtItem->execute([$cartId, $productId]);
$item = $stmtItem->fetch();

try {
    if ($action === 'add' || $action === 'update') {
        if ($action === 'add') {
             $newQty = $item ? $item['quantity'] + $quantity : $quantity;
        } else {
             $newQty = $quantity;
        }

        if ($newQty > $stock) {
            echo json_encode(['success' => false, 'message' => "Not enough stock. Only $stock left."]);
            exit;
        }
        
        if ($newQty <= 0) {
            // Remove if 0 or less
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")->execute([$cartId, $productId]);
        } else if ($item) {
            $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$newQty, $item['id']]);
        } else {
            $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$cartId, $productId, $newQty]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else if ($action === 'remove') {
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")->execute([$cartId, $productId]);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
