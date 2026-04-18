<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Determine base URL for links when in subdirectories (e.g. /admin/)
$baseUrl = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? '../' : '';

// Active page helper
$currentPage = basename($_SERVER['PHP_SELF']);
function navClass($page, $current) {
    return $page === $current 
        ? 'border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium';
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopSphere — Premium E-Commerce</title>
    <meta name="description" content="Shop the best electronics, clothing, books, home goods, and sports equipment at ShopSphere.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: {
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%':   { transform: 'translate(0px, 0px) scale(1)' },
                            '33%':  { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%':  { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/style.css">
</head>
<body class="min-h-full flex flex-col font-sans text-gray-900 bg-white">

<!-- ===========================
     NAVIGATION BAR
     =========================== -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LEFT: Logo + Desktop Links -->
            <div class="flex items-center">
                <!-- Logo -->
                <a href="<?= $baseUrl ?>index.php" class="flex-shrink-0 text-2xl font-extrabold text-indigo-600 tracking-tight mr-8">
                    ShopSphere
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?= $baseUrl ?>index.php"
                       class="<?= navClass('index.php', $currentPage) ?> px-3 py-2 rounded-md text-sm font-medium transition-colors border-0 hover:bg-gray-100">
                        Home
                    </a>
                    <a href="<?= $baseUrl ?>shop.php"
                       class="<?= navClass('shop.php', $currentPage) ?> px-3 py-2 rounded-md text-sm font-medium transition-colors border-0 hover:bg-gray-100">
                        Shop
                    </a>

                    <!-- Categories Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                            Categories
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Dropdown panel -->
                        <div class="absolute left-0 top-full mt-1 w-52 bg-white rounded-xl shadow-xl border border-gray-100 hidden group-hover:block z-50 overflow-hidden">
                            <a href="<?= $baseUrl ?>shop.php?category=electronics"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <span class="text-xl">💻</span> Electronics
                            </a>
                            <a href="<?= $baseUrl ?>shop.php?category=clothing"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <span class="text-xl">👗</span> Clothing
                            </a>
                            <a href="<?= $baseUrl ?>shop.php?category=books"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <span class="text-xl">📚</span> Books
                            </a>
                            <a href="<?= $baseUrl ?>shop.php?category=home-living"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <span class="text-xl">🏡</span> Home & Living
                            </a>
                            <a href="<?= $baseUrl ?>shop.php?category=sports"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <span class="text-xl">⚽</span> Sports
                            </a>
                        </div>
                    </div>

                    <a href="<?= $baseUrl ?>new-arrivals.php"
                       class="<?= navClass('new-arrivals.php', $currentPage) ?> px-3 py-2 rounded-md text-sm font-medium transition-colors border-0 hover:bg-gray-100">
                        ✨ New Arrivals
                    </a>
                    <a href="<?= $baseUrl ?>sale.php"
                       class="px-3 py-2 rounded-md text-sm font-semibold transition-colors border-0 text-orange-500 hover:bg-orange-50 hover:text-orange-700">
                        🔥 Sale
                    </a>

                    <?php if (isAdmin()): ?>
                        <a href="<?= $baseUrl ?>admin/index.php"
                           class="px-3 py-2 rounded-md text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition-colors border-0">
                            ⚙ Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Cart + User -->
            <div class="flex items-center gap-3">
                <!-- Cart Icon -->
                <a href="<?= $baseUrl ?>cart.php" class="relative p-2 text-gray-400 hover:text-indigo-600 transition-colors rounded-full hover:bg-gray-100" title="Cart">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </a>

                <?php if (isLoggedIn()): ?>
                    <!-- User Avatar Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-sm rounded-full focus:outline-none">
                            <span class="bg-indigo-600 text-white h-9 w-9 rounded-full flex items-center justify-center font-bold text-sm shadow-sm">
                                <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
                            </span>
                        </button>
                        <div class="absolute right-0 top-full mt-2 w-60 bg-white rounded-xl shadow-xl border border-gray-100 hidden group-hover:block z-50 overflow-hidden">
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-900 truncate"><?= sanitize($_SESSION['name'] ?? '') ?></p>
                                    <?php if (isAdmin()): ?>
                                        <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">ADMIN</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-400 truncate mt-0.5"><?= sanitize($_SESSION['email'] ?? '') ?></p>
                            </div>

                            <!-- Customer links -->
                            <a href="<?= $baseUrl ?>profile.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Your Profile
                            </a>
                            <a href="<?= $baseUrl ?>orders.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Order History
                            </a>

                            <?php if (isAdmin()): ?>
                            <!-- Admin-only section -->
                            <div class="border-t border-indigo-100 mt-1 bg-indigo-50">
                                <p class="px-4 pt-2 pb-1 text-xs font-semibold text-indigo-400 uppercase tracking-wider">Admin Tools</p>
                                <a href="<?= $baseUrl ?>admin/index.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-100 transition-colors font-medium">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Admin Dashboard
                                </a>
                                <a href="<?= $baseUrl ?>admin/products.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-100 transition-colors font-medium">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Manage Products
                                </a>
                                <a href="<?= $baseUrl ?>admin/edit_product.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-100 transition-colors font-medium">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add New Product
                                </a>
                                <a href="<?= $baseUrl ?>admin/users.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-100 transition-colors font-medium">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    Manage Users
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- Sign out -->
                            <div class="border-t border-gray-100">
                                <a href="<?= $baseUrl ?>logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>login.php"
                       class="hidden md:inline text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        Log in
                    </a>
                    <a href="<?= $baseUrl ?>register.php"
                       class="hidden md:inline text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg shadow transition-colors">
                        Sign up
                    </a>
                <?php endif; ?>

                <!-- Mobile Hamburger -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition-colors" aria-label="Open menu">
                    <svg id="icon-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="icon-close" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="<?= $baseUrl ?>index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">Home</a>
            <a href="<?= $baseUrl ?>shop.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">Shop</a>
            <div class="pt-1 border-t border-gray-100">
                <p class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Categories</p>
                <a href="<?= $baseUrl ?>shop.php?category=electronics" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">💻 Electronics</a>
                <a href="<?= $baseUrl ?>shop.php?category=clothing"    class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">👗 Clothing</a>
                <a href="<?= $baseUrl ?>shop.php?category=books"       class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">📚 Books</a>
                <a href="<?= $baseUrl ?>shop.php?category=home-living" class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">🏡 Home & Living</a>
                <a href="<?= $baseUrl ?>shop.php?category=sports"      class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">⚽ Sports</a>
            </div>
            <a href="<?= $baseUrl ?>new-arrivals.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100">✨ New Arrivals</a>
            <a href="<?= $baseUrl ?>sale.php"         class="block px-3 py-2 rounded-md text-base font-semibold text-orange-500 hover:bg-orange-50">🔥 Sale</a>
            <?php if (isAdmin()): ?>
                <a href="<?= $baseUrl ?>admin/index.php" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-600 hover:bg-indigo-50">⚙ Admin Panel</a>
            <?php endif; ?>
            <div class="pt-2 border-t border-gray-100">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <div class="px-3 py-1 flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Account</span>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">ADMIN</span>
                        </div>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>profile.php" class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">👤 Your Profile</a>
                    <a href="<?= $baseUrl ?>orders.php"  class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">📦 Order History</a>
                    <?php if (isAdmin()): ?>
                        <div class="mt-2 pt-2 border-t border-indigo-100 bg-indigo-50 rounded-lg mx-1 px-2 pb-2">
                            <p class="px-1 pt-2 pb-1 text-xs font-semibold text-indigo-400 uppercase tracking-wider">Admin Tools</p>
                            <a href="<?= $baseUrl ?>admin/index.php"        class="block px-3 py-2 rounded-md text-sm text-indigo-700 font-medium hover:bg-indigo-100">⚙ Admin Dashboard</a>
                            <a href="<?= $baseUrl ?>admin/products.php"     class="block px-3 py-2 rounded-md text-sm text-indigo-700 font-medium hover:bg-indigo-100">🛍 Manage Products</a>
                            <a href="<?= $baseUrl ?>admin/edit_product.php" class="block px-3 py-2 rounded-md text-sm text-indigo-700 font-medium hover:bg-indigo-100">➕ Add New Product</a>
                            <a href="<?= $baseUrl ?>admin/users.php"        class="block px-3 py-2 rounded-md text-sm text-indigo-700 font-medium hover:bg-indigo-100">👥 Manage Users</a>
                        </div>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>logout.php" class="block px-3 py-2 mt-1 rounded-md text-sm text-red-600 hover:bg-red-50">🚪 Sign out</a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>login.php"    class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">Log in</a>
                    <a href="<?= $baseUrl ?>register.php" class="block px-3 py-2 rounded-md text-sm font-semibold text-indigo-600 hover:bg-indigo-50">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
    <?= displayFlashMessages() ?>
</div>

<!-- Mobile menu toggle script -->
<script>
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('icon-open');
    const iconClose = document.getElementById('icon-close');
    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        iconOpen.classList.toggle('hidden');
        iconClose.classList.toggle('hidden');
    });
</script>
