<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_new = 1
    ORDER BY p.created_at DESC
");
$newProducts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Page Header -->
        <div class="mb-10">
            <div class="flex items-center space-x-3 mb-2">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">✨ New</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">New Arrivals</h1>
            <p class="mt-2 text-gray-500 max-w-2xl">The freshest products just added to our store — be the first to shop our latest collection across all categories.</p>
        </div>

        <?php if (empty($newProducts)): ?>
            <div class="text-center py-24 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-gray-500">No new arrivals at the moment. Check back soon!</p>
            </div>
        <?php else: ?>
            <!-- Group by category -->
            <?php
            $grouped = [];
            foreach ($newProducts as $p) {
                $grouped[$p['category_name']][] = $p;
            }
            ?>
            <?php foreach ($grouped as $catName => $catProducts): ?>
                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900"><?= sanitize($catName) ?></h2>
                        <div class="ml-4 flex-1 h-px bg-gray-200"></div>
                        <a href="shop.php?category=<?= urlencode($catProducts[0]['category_slug']) ?>" class="ml-4 text-sm font-medium text-indigo-600 hover:text-indigo-800 whitespace-nowrap">
                            See all →
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                        <?php foreach ($catProducts as $product): ?>
                            <div class="group bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden">
                                <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                                    <span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">NEW</span>
                                    <img src="<?= getProductImage($product) ?>"
                                         alt="<?= sanitize($product['name']) ?>"
                                         class="w-full h-52 object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">
                                        <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-indigo-600">
                                            <?= sanitize($product['name']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-3 flex-1 line-clamp-2"><?= sanitize($product['description']) ?></p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-lg font-bold text-indigo-600"><?= formatPrice($product['price']) ?></span>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button onclick="addToCart(<?= $product['id'] ?>)" class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1.5 text-sm rounded transition-colors">
                                                Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-red-500">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
