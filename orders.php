<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;

function getStatusClasses($status) {
    return match($status) {
        'pending' => 'bg-yellow-100 text-yellow-800',
        'paid' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
}

if ($orderId) {
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        setFlash('error', 'Order not found.');
        redirect('orders.php');
    }
    
    
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.name, p.slug 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$orderId]);
    $items = $stmtItems->fetchAll();
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
         if (in_array($order['status'], ['pending', 'paid'])) {
             $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$orderId]);
             setFlash('success', 'Order has been successfully cancelled.');
             redirect("orders.php?id=$orderId");
         } else {
             setFlash('error', 'This order cannot be cancelled at this stage.');
         }
    }
} else {
    
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 flex-1 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if ($orderId): ?>
            
            <div class="mb-4">
                <a href="orders.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to all orders</a>
            </div>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Order #<?= $order['id'] ?> Details</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full uppercase tracking-wider <?= getStatusClasses($order['status']) ?>">
                            <?= sanitize($order['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold text-lg"><?= formatPrice($order['total_amount']) ?></dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Items Purchased</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <ul role="list" class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    <?php foreach ($items as $item): ?>
                                    <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                        <div class="w-0 flex-1 flex items-center">
                                            <span class="ml-2 flex-1 w-0 truncate">
                                                <a href="product.php?slug=<?= sanitize($item['slug']) ?>" class="hover:text-indigo-600">
                                                    <?= sanitize($item['name']) ?>
                                                </a>
                                                <span class="text-gray-500 ml-2">x <?= $item['quantity'] ?></span>
                                            </span>
                                        </div>
                                        <div class="ml-4 flex-shrink-0 font-medium">
                                            <?= formatPrice($item['unit_price']) ?>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </div>
                    </dl>
                </div>
                
                <?php if (in_array($order['status'], ['pending', 'paid'])): ?>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6 text-right">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <?= csrfField() ?>
                            <button type="submit" name="cancel_order" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                                Cancel Order
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-8">Order History</h1>
            
            <?php if (empty($orders)): ?>
                <div class="text-center py-20 bg-white shadow rounded-lg border border-gray-200">
                    <p class="text-lg text-gray-500 mb-4">You haven't placed any orders yet.</p>
                    <a href="shop.php" class="text-indigo-600 font-medium hover:text-indigo-500">Go to Shop</a>
                </div>
            <?php else: ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
                    <ul role="list" class="divide-y divide-gray-200">
                        <?php foreach ($orders as $order): ?>
                            <li>
                                <a href="orders.php?id=<?= $order['id'] ?>" class="block hover:bg-gray-50 transition-colors">
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-indigo-600 truncate">
                                                Order #<?= $order['id'] ?>
                                            </p>
                                            <div class="ml-2 flex-shrink-0 flex">
                                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full uppercase <?= getStatusClasses($order['status']) ?>">
                                                    <?= sanitize($order['status']) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2 sm:flex sm:justify-between">
                                            <div class="sm:flex">
                                                <p class="flex items-center text-sm text-gray-500">
                                                    <?= formatPrice($order['total_amount']) ?>
                                                </p>
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                <p>
                                                    Ordered on <time datetime="<?= $order['created_at'] ?>"><?= date('M j, Y', strtotime($order['created_at'])) ?></time>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
