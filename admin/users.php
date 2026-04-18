<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $deleteId = (int)$_POST['delete_user_id'];
        if ($deleteId === $_SESSION['user_id']) {
            setFlash('error', 'You cannot delete your own account.');
        } else {
            $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$deleteId]);
            setFlash('success', 'Customer account deleted successfully.');
        }
        redirect('users.php');
    }
}

$stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="bg-gray-50 flex-1 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">Manage Users</h1>
                <p class="mt-2 text-sm text-gray-700">View and manage registered accounts.</p>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Registered On</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                <?= sanitize($user['name']) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <a href="mailto:<?= sanitize($user['email']) ?>" class="text-indigo-600 hover:text-indigo-900"><?= sanitize($user['email']) ?></a>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Admin</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <?= date('M j, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-right pr-6">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this customer? This will cascades delete their orders and carts too!');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
