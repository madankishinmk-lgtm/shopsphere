<?php
// ============================================================
// FILE: index.php  |  Home / Landing Page
// TABLES USED  : products (FK -> categories), categories
// CRUD COVERED : READ (featured products, new arrivals, sale items, categories)
// REQUIREMENT  : Uses JOIN across 3+ connected tables with foreign keys ✓
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// READ: Fetch all product categories
// REQUIREMENT: READ operation on 'categories' table

$stmt = $pdo->query("SELECT * FROM categories LIMIT 6");
$categories = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id ASC
    LIMIT 8
");
$featuredProducts = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_new = 1
    ORDER BY p.id DESC
    LIMIT 4
");
$newProducts = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_sale = 1
    ORDER BY (p.original_price - p.price) DESC
    LIMIT 4
");
$saleProducts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="relative bg-gradient-to-br from-indigo-50 via-white to-purple-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="lg:flex lg:items-center lg:gap-x-16">
            
            <div class="flex-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 mb-6">
                    ✨ New arrivals just dropped
                </span>
                <h1 class="text-5xl lg:text-7xl font-extrabold text-gray-900 leading-tight tracking-tight">
                    Premium products<br>
                    <span class="text-indigo-600">for your everyday</span>
                </h1>
                <p class="mt-6 text-xl text-gray-500 max-w-2xl">
                    Discover the best tech, fashion, books, and home goods — all in one place. Built carefully to deliver exceptional quality directly to your doorstep.
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="shop.php" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition-all duration-200 text-lg">
                        Shop Now →
                    </a>
                    <a href="sale.php" class="px-8 py-4 bg-orange-100 hover:bg-orange-200 text-orange-700 font-semibold rounded-xl transition-all duration-200 text-lg">
                        🔥 View Sale
                    </a>
                    <a href="#categories" class="px-8 py-4 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 text-lg shadow-sm">
                        Browse Categories
                    </a>
                </div>
            </div>

            
            <div class="hidden lg:flex flex-1 gap-4 mt-12 lg:mt-0 relative">
                <div class="flex flex-col gap-4 pt-8">
                    <div class="w-44 h-44 rounded-2xl overflow-hidden shadow-xl">
                        <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=400&auto=format&fit=crop" alt="Smartphone" class="w-full h-full object-cover">
                    </div>
                    <div class="w-44 h-44 rounded-2xl overflow-hidden shadow-xl">
                        <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=400&auto=format&fit=crop" alt="Shoes" class="w-full h-full object-cover">
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="w-44 h-44 rounded-2xl overflow-hidden shadow-xl">
                        <img src="https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=400&auto=format&fit=crop" alt="Laptop" class="w-full h-full object-cover">
                    </div>
                    <div class="w-44 h-44 rounded-2xl overflow-hidden shadow-xl">
                        <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=400&auto=format&fit=crop" alt="Books" class="w-full h-full object-cover">
                    </div>
                </div>
                
                <div class="absolute -top-10 -right-10 w-48 h-48 bg-purple-200 rounded-full mix-blend-multiply filter blur-2xl opacity-60"></div>
                <div class="absolute -bottom-10 right-10 w-48 h-48 bg-indigo-200 rounded-full mix-blend-multiply filter blur-2xl opacity-60"></div>
            </div>
        </div>
    </div>
</div>

<div id="categories" class="bg-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8">Shop by Category</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php
            $catIcons = ['Electronics' => '💻', 'Clothing' => '👗', 'Books' => '📚', 'Home & Living' => '🏡', 'Sports' => '⚽'];
            $catColors = ['Electronics' => 'bg-blue-50 text-blue-700 border-blue-200', 'Clothing' => 'bg-pink-50 text-pink-700 border-pink-200', 'Books' => 'bg-yellow-50 text-yellow-700 border-yellow-200', 'Home & Living' => 'bg-green-50 text-green-700 border-green-200', 'Sports' => 'bg-orange-50 text-orange-700 border-orange-200'];
            foreach ($categories as $category):
                $icon = $catIcons[$category['name']] ?? '🛍️';
                $color = $catColors[$category['name']] ?? 'bg-gray-50 text-gray-700 border-gray-200';
            ?>
                <a href="shop.php?category=<?= urlencode($category['slug']) ?>"
                   class="flex flex-col items-center p-6 rounded-2xl border <?= $color ?> hover:shadow-md transition-all duration-200 group">
                    <span class="text-4xl mb-3 group-hover:scale-125 transition-transform duration-200"><?= $icon ?></span>
                    <span class="text-sm font-semibold text-center"><?= sanitize($category['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="bg-gray-50 py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">Featured Products</h2>
            <a href="shop.php" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">View all →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="group bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden">
                    
                    <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                        <?php if ($product['stock'] <= 0): ?>
                            <span class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow z-10">Out of Stock</span>
                        <?php elseif (!empty($product['is_sale']) && $product['is_sale']): ?>
                            <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow z-10">SALE</span>
                        <?php elseif (!empty($product['is_new']) && $product['is_new']): ?>
                            <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow z-10">NEW</span>
                        <?php endif; ?>
                        <span class="absolute top-2 left-2 bg-white/90 text-indigo-700 text-xs font-semibold px-2 py-1 rounded-full shadow z-10"><?= sanitize($product['category_name']) ?></span>
                        <img src="<?= getProductImage($product) ?>"
                             alt="<?= sanitize($product['name']) ?>"
                             class="w-full h-52 object-cover object-center group-hover:scale-110 transition-transform duration-500">
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/edit_product.php?id=<?= $product['id'] ?>"
                           class="absolute bottom-2 right-2 z-20 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-lg transition-colors opacity-0 group-hover:opacity-100"
                           title="Admin: Edit product">
                            ✏ Edit
                        </a>
                    <?php endif; ?>
                    
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-base font-semibold text-gray-900 mb-1 line-clamp-1">
                            <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-indigo-600 transition-colors">
                                <?= sanitize($product['name']) ?>
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500 mb-3 flex-1 line-clamp-2"><?= sanitize($product['description']) ?></p>
                        <div class="flex items-center justify-between mt-auto">
                            <div>
                                <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="text-xs text-gray-400 line-through"><?= formatPrice($product['original_price']) ?></span><br>
                                <?php endif; ?>
                                <span class="text-lg font-bold text-indigo-600"><?= formatPrice($product['price']) ?></span>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                                <button onclick="addToCart(<?= $product['id'] ?>)"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white p-2.5 rounded-full transition-colors shadow"
                                        title="Add to Cart">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!empty($saleProducts)): ?>
<div class="bg-gradient-to-r from-orange-500 to-red-600 py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-orange-100 text-sm font-semibold tracking-widest uppercase mb-1">Limited Time</p>
                <h2 class="text-3xl font-extrabold text-white">🔥 On Sale Now</h2>
            </div>
            <a href="sale.php" class="bg-white text-orange-600 hover:bg-orange-50 font-semibold px-5 py-2.5 rounded-xl shadow transition-colors">
                View All Deals →
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($saleProducts as $product):
                $discount = 0;
                if (!empty($product['original_price']) && $product['original_price'] > 0) {
                    $discount = round((1 - $product['price'] / $product['original_price']) * 100);
                }
            ?>
                <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden">
                    <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                        <?php if ($discount > 0): ?>
                            <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow z-10">-<?= $discount ?>%</span>
                        <?php endif; ?>
                        <img src="<?= getProductImage($product) ?>"
                             alt="<?= sanitize($product['name']) ?>"
                             class="w-full h-44 object-cover object-center group-hover:scale-110 transition-transform duration-500">
                    </a>
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-1">
                            <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-orange-600"><?= sanitize($product['name']) ?></a>
                        </h3>
                        <div class="mt-auto flex items-center justify-between pt-2">
                            <div>
                                <?php if (!empty($product['original_price'])): ?>
                                    <span class="text-xs text-gray-400 line-through"><?= formatPrice($product['original_price']) ?></span><br>
                                <?php endif; ?>
                                <span class="text-base font-bold text-orange-600"><?= formatPrice($product['price']) ?></span>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                                <button onclick="addToCart(<?= $product['id'] ?>)"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                                    Add to Cart
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($newProducts)): ?>
<div class="bg-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-green-600 text-sm font-semibold tracking-widest uppercase mb-1">Just dropped</p>
                <h2 class="text-3xl font-extrabold text-gray-900">✨ New Arrivals</h2>
            </div>
            <a href="new-arrivals.php" class="bg-green-50 text-green-700 hover:bg-green-100 font-semibold px-5 py-2.5 rounded-xl border border-green-200 transition-colors">
                See All New →
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($newProducts as $product): ?>
                <div class="group bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden">
                    <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                        <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow z-10">NEW</span>
                        <img src="<?= getProductImage($product) ?>"
                             alt="<?= sanitize($product['name']) ?>"
                             class="w-full h-44 object-cover object-center group-hover:scale-110 transition-transform duration-500">
                    </a>
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-1">
                            <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-green-600"><?= sanitize($product['name']) ?></a>
                        </h3>
                        <div class="mt-auto flex items-center justify-between pt-2">
                            <span class="text-base font-bold text-indigo-600"><?= formatPrice($product['price']) ?></span>
                            <?php if ($product['stock'] > 0): ?>
                                <button onclick="addToCart(<?= $product['id'] ?>)"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-medium rounded-lg transition-colors">
                                    Add to Cart
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
