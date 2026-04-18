<?php
// ============================================================
// FILE: cart_action.php  |  AJAX Cart Handler
// TABLES USED  : carts (FK -> users), cart_items (FK -> carts + products)
// CRUD COVERED : CREATE (add item to cart), UPDATE (change quantity),
//                DELETE (remove item from cart), READ (check stock)
// REQUIREMENT  : All 4 CRUD operations demonstrated across tables ✓
// ============================================================
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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$action = $input['action'] ?? '';
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);

if (!$productId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// READ: Find user's cart (FK: carts.user_id -> users.id)
$stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmtCart->execute([$_SESSION['user_id']]);
$cart = $stmtCart->fetch();

if (!$cart) {
    // CREATE: Auto-create cart if user doesn't have one yet
    // REQUIREMENT: CREATE on 'carts' table | FK carts.user_id -> users.id
    $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)")->execute([$_SESSION['user_id']]);
    $cartId = $pdo->lastInsertId();
} else {
    $cartId = $cart['id'];
}

$stmtProd = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmtProd->execute([$productId]);
$product = $stmtProd->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$stock = $product['stock'];

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
            // DELETE: Remove item when quantity drops to 0
            // REQUIREMENT: DELETE operation on 'cart_items' table
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")->execute([$cartId, $productId]);
        } else if ($item) {
            // UPDATE: Change quantity of an existing cart item
            // REQUIREMENT: UPDATE operation on 'cart_items' table
            $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$newQty, $item['id']]);
        } else {
            // CREATE: Add a new item to the cart
            // REQUIREMENT: CREATE operation on 'cart_items' table
            // FK: cart_items.cart_id -> carts.id | cart_items.product_id -> products.id
            $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$cartId, $productId, $newQty]);
        }

        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else if ($action === 'remove') {
        // DELETE: Explicitly remove an item from cart
        // REQUIREMENT: DELETE operation on 'cart_items' table
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")->execute([$cartId, $productId]);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
