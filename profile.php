<?php
// ============================================================
// FILE: profile.php  |  Customer Profile Settings
// TABLES USED  : users
// CRUD COVERED : READ (fetch current user data)
//                UPDATE (save name, email, and/or password)
// REQUIREMENT  : UPDATE operation on 'users' table ✓
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];
$errors = [];

// READ: Fetch the current user's profile data
// REQUIREMENT: READ operation on 'users' table
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($name)) $errors[] = "Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        
        
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$email, $userId]);
        if ($stmtCheck->fetch()) {
            $errors[] = "Email is already in use by another account.";
        }

        if (!empty($password)) {
            if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
            if ($password !== $password_confirm) $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {
            if (!empty($password)) {
                // UPDATE: Save name, email, and new password hash
                // REQUIREMENT: UPDATE operation on 'users' table (with password change)
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?");
                $stmtUpdate->execute([$name, $email, $hash, $userId]);
            } else {
                // UPDATE: Save name and email only (no password change)
                // REQUIREMENT: UPDATE operation on 'users' table
                $stmtUpdate = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmtUpdate->execute([$name, $email, $userId]);
            }
            
            
            $_SESSION['name'] = $name;
            setFlash('success', 'Profile updated successfully.');
            redirect('profile.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 flex-1 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-8">Profile Settings</h1>

        <?php if (!empty($errors)): ?>
             <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                 <ul class="list-disc pl-5">
                     <?php foreach ($errors as $err): ?>
                         <li><?= sanitize($err) ?></li>
                     <?php endforeach; ?>
                 </ul>
             </div>
        <?php endif; ?>

        <div class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <form action="profile.php" method="POST" class="space-y-6">
                    <?= csrfField() ?>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="<?= sanitize($_POST['name'] ?? $user['name']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input type="email" name="email" id="email" value="<?= sanitize($_POST['email'] ?? $user['email']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                        <p class="text-sm text-gray-500 mb-4">Leave blank if you do not wish to change your password.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="password_confirm" id="password_confirm" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-6 inline-flex justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
