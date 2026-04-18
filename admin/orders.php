<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $orderId = (int)$_POST['order_id'];
        $status = $_POST['status'];
        
        $allowedStatuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
        if (in_array($status, $allowedStatuses)) {
            $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $orderId]);
            setFlash('success', "Order #$orderId status updated to $status.");
            redirect('orders.php');
        }
    }
}

$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-gray-50 flex-1 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">Manage Orders</h1>
                <p class="mt-2 text-sm text-gray-700">Update the fulfillment status of customer orders.</p>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
            <?php if (empty($orders)): ?>
                <div class="p-8 text-center text-gray-500">No orders found.</div>
            <?php else: ?>
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Order ID</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Update Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    #<?= $order['id'] ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="font-medium text-gray-900"><?= sanitize($order['customer_name']) ?></div>
                                    <div class="text-gray-500"><?= sanitize($order['email']) ?></div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <?= date('M j, Y h:ia', strtotime($order['created_at'])) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                    <?= formatPrice($order['total_amount']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <form method="POST" action="orders.php" class="flex items-center space-x-2">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6 px-3">
                                            <?php foreach (['pending', 'paid', 'shipped', 'completed', 'cancelled'] as $st): ?>
                                                <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-1 px-3 border border-gray-300 rounded shadow-sm text-sm transition-colors">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
