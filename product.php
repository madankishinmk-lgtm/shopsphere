<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('shop.php');
}

// Fetch basic product info
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='text-center py-20'><h1 class='text-3xl font-bold text-gray-900'>Product not found</h1><a href='shop.php' class='text-indigo-600 hover:text-indigo-800 mt-4 inline-block'>Return to shop</a></div>";
    exit;
}

// Shared helper: add product to cart and return cartId or false
function addProductToCart($pdo, $userId, $productId, $quantity, $stock) {
    if ($quantity <= 0 || $quantity > $stock) return false;

    // Ensure cart exists
    $stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmtCart->execute([$userId]);
    $cart = $stmtCart->fetch();
    if (!$cart) {
        $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)")->execute([$userId]);
        $cartId = $pdo->lastInsertId();
    } else {
        $cartId = $cart['id'];
    }

    // Upsert cart item
    $stmtItem = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmtItem->execute([$cartId, $productId]);
    $item = $stmtItem->fetch();
    if ($item) {
        $newQty = min($item['quantity'] + $quantity, $stock);
        $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$newQty, $item['id']]);
    } else {
        $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$cartId, $productId, $quantity]);
    }
    return true;
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = "product.php?slug=" . urlencode($slug);
        setFlash('warning', 'Please log in to add items to your cart.');
        redirect('login.php');
    }
    $quantity = (int)($_POST['quantity'] ?? 1);
    if (addProductToCart($pdo, $_SESSION['user_id'], $product['id'], $quantity, $product['stock'])) {
        setFlash('success', 'Added to cart successfully!');
        redirect('cart.php');
    } else {
        setFlash('error', 'Invalid quantity.');
    }
}

// Handle Buy Now — add to cart then go straight to checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = "product.php?slug=" . urlencode($slug);
        setFlash('warning', 'Please log in to purchase.');
        redirect('login.php');
    }
    $quantity = (int)($_POST['quantity'] ?? 1);
    if (addProductToCart($pdo, $_SESSION['user_id'], $product['id'], $quantity, $product['stock'])) {
        redirect('checkout.php');
    } else {
        setFlash('error', 'Invalid quantity.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <div class="pt-6 pb-16 sm:pb-24">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 mt-4">
            <ol role="list" class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="index.php" class="text-gray-400 hover:text-gray-500">Home</a>
                </li>
                <li>
                    <span class="text-gray-300">/</span>
                </li>
                <li>
                    <a href="shop.php" class="text-gray-400 hover:text-gray-500">Shop</a>
                </li>
                <li>
                    <span class="text-gray-300">/</span>
                </li>
                <li>
                    <a href="shop.php?category=<?= urlencode(strtolower(str_replace(' ', '-', $product['category_name']))) ?>" class="text-gray-400 hover:text-gray-500"><?= sanitize($product['category_name']) ?></a>
                </li>
                <li>
                    <span class="text-gray-300">/</span>
                </li>
                <li class="text-gray-900 font-medium" aria-current="page"><?= sanitize($product['name']) ?></li>
            </ol>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
                
                <!-- Image gallery -->
                <div class="flex-col-reverse">
                    <div class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden flex justify-center items-center h-96">
                        <img src="<?= getProductImage($product) ?>" 
                             alt="<?= sanitize($product['name']) ?>"
                             class="w-full h-full object-cover object-center">
                    </div>
                </div>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900"><?= sanitize($product['name']) ?></h1>
                    
                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-3xl text-indigo-600 font-bold"><?= formatPrice($product['price']) ?></p>
                        
                        <div class="ml-4 pl-4 border-l border-gray-300">
                             <?php if ($product['stock'] > 0): ?>
                                <p class="text-green-600 font-medium">In Stock (<?= $product['stock'] ?> left)</p>
                            <?php else: ?>
                                <p class="text-red-600 font-medium font-bold">Out of Stock</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="sr-only">Description</h3>
                        <div class="text-base text-gray-700 space-y-6">
                            <p><?= nl2br(sanitize($product['description'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8 border-t border-gray-200 pt-8">
                         <div class="flex items-center gap-4">
                             <img src="https://ui-avatars.com/api/?name=<?= urlencode($product['category_name']) ?>&background=random" alt="" class="h-10 w-10 rounded-full">
                             <div>
                                 <p class="text-sm font-medium text-gray-900">Category</p>
                                 <p class="text-sm text-gray-500"><?= sanitize($product['category_name']) ?></p>
                             </div>
                         </div>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <form class="mt-8 flex gap-4" method="POST">
                            <div class="w-32">
                                <label for="quantity" class="sr-only">Quantity</label>
                                <select id="quantity" name="quantity" class="max-w-full rounded-md border border-gray-300 py-3 px-4 text-base leading-medium font-medium text-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <?php for($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" name="add_to_cart" class="flex-1 bg-indigo-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                Add to Cart
                            </button>
                            
                            <button type="submit" name="buy_now" class="bg-gray-100 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-indigo-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                Buy Now
                            </button>
                        </form>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
