<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$userId = $_SESSION['user_id'];

// Get cart info
$stmtCart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmtCart->execute([$userId]);
$cart = $stmtCart->fetch();
$cartId = $cart ? $cart['id'] : null;

// Get Cart Items
$stmtItems = $pdo->prepare("
    SELECT ci.quantity, p.id as product_id, p.name, p.price 
    FROM cart_items ci JOIN products p ON ci.product_id = p.id 
    WHERE ci.cart_id = ?
");
$stmtItems->execute([$cartId]);
$cartItems = $stmtItems->fetchAll();

if (empty($cartItems)) {
    setFlash('warning', 'Your cart is empty.');
    redirect('cart.php');
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 50 ? 0 : 10;
$total = $subtotal + $shipping;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        // Here we'd validate the shipping address and payment info.
        // For the scope of this project, we'll assume it's valid and proceed to create the order.
        
        try {
            $pdo->beginTransaction();
            
            // 1. Create Order
            $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'paid')");
            $stmtOrder->execute([$userId, $total]);
            $orderId = $pdo->lastInsertId();
            
            // 2. Insert Order Items & Update Stock
            $stmtInsertItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            foreach ($cartItems as $item) {
                // Ignore stock constraint failures here for simplicity, in real app need strict locking
                $stmtInsertItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
                $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
            }
            
            // 3. Clear Cart
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);
            
            $pdo->commit();
            
            setFlash('success', 'Order placed successfully! Thank you for your purchase.');
            redirect('orders.php?id=' . $orderId);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "An error occurred while placing your order. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-8">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
            <div class="lg:col-span-7">
                <form id="checkout-form" method="POST" action="checkout.php" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 space-y-8">
                    <?= csrfField() ?>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Contact information</h2>
                        <div class="mt-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <input type="email" id="email" name="email" value="<?= sanitize($_SESSION['name']) ?> (Logged in)" disabled class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm opacity-70 px-3 py-2">
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping address (Demo)</h2>
                        <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <div class="sm:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" id="address" name="address" required placeholder="123 Main St" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <input type="text" id="city" name="city" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal code</label>
                                <input type="text" id="postal_code" name="postal_code" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Payment (Mock)</h2>
                        <div class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-4 sm:gap-x-4">
                            <div class="sm:col-span-4 block text-sm font-medium text-gray-700 mb-2">
                                <div class="p-4 bg-indigo-50 border border-indigo-100 rounded text-indigo-700 text-sm">
                                    Simulating payment processor. No real card needed.
                                </div>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="card_number" class="block text-sm font-medium text-gray-700">Card number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-8">
                        <button type="submit" class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500 transition-colors">
                            Confirm order &middot; <?= formatPrice($total) ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-5 mt-10 lg:mt-0">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-24">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Summary</h2>
                    
                    <ul role="list" class="divide-y divide-gray-200 max-h-96 overflow-y-auto pr-2">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="py-4 flex">
                                <div class="ml-3 flex-1 flex flex-col justify-center">
                                    <h3 class="text-sm font-medium text-gray-900"><?= sanitize($item['name']) ?></h3>
                                    <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                </div>
                                <p class="text-sm font-medium text-gray-900 ml-4"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <dl class="space-y-4 text-sm text-gray-600 border-t border-gray-200 pt-6 mt-6">
                        <div class="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd class="font-medium text-gray-900"><?= formatPrice($subtotal) ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Shipping</dt>
                            <dd class="font-medium text-gray-900"><?= $shipping === 0 ? 'Free' : formatPrice($shipping) ?></dd>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-4">
                            <dt class="text-base font-medium text-gray-900">Total</dt>
                            <dd class="text-xl font-bold text-indigo-600"><?= formatPrice($total) ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
