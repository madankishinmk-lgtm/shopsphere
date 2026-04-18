<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_sale = 1
    ORDER BY (p.original_price - p.price) DESC
");
$saleProducts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <!-- Sale Banner -->
    <div class="bg-gradient-to-r from-orange-500 to-red-600 py-8 px-4 text-center text-white">
        <p class="text-sm font-semibold tracking-widest uppercase mb-1">Limited Time Offer</p>
        <h1 class="text-4xl font-extrabold mb-2">🔥 Sale & Promotions</h1>
        <p class="text-lg opacity-90">Huge discounts on top products grab them before they're gone!</p>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <?php if (empty($saleProducts)): ?>
            <div class="text-center py-24 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-gray-500">No sale items currently. Check back soon!</p>
            </div>
        <?php else: ?>
            <!-- Group by category -->
            <?php
            $grouped = [];
            foreach ($saleProducts as $p) {
                $grouped[$p['category_name']][] = $p;
            }
            ?>
            <?php foreach ($grouped as $catName => $catProducts): ?>
                <div class="mb-12">
                    <div class="flex items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900"><?= sanitize($catName) ?></h2>
                        <div class="ml-4 flex-1 h-px bg-gray-200"></div>
                        <a href="shop.php?category=<?= urlencode($catProducts[0]['category_slug']) ?>" class="ml-4 text-sm font-medium text-orange-600 hover:text-orange-800 whitespace-nowrap">
                            See all →
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                        <?php foreach ($catProducts as $product): ?>
                            <?php
                            $discount = 0;
                            if (!empty($product['original_price']) && $product['original_price'] > 0) {
                                $discount = round((1 - $product['price'] / $product['original_price']) * 100);
                            }
                            ?>
                            <div class="group bg-white border border-orange-200 rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden">
                                <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                                    <?php if ($discount > 0): ?>
                                        <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">-<?= $discount ?>%</span>
                                    <?php endif; ?>
                                    <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">SALE</span>
                                    <img src="<?= getProductImage($product) ?>"
                                         alt="<?= sanitize($product['name']) ?>"
                                         class="w-full h-52 object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">
                                        <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-orange-600">
                                            <?= sanitize($product['name']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-3 flex-1 line-clamp-2"><?= sanitize($product['description']) ?></p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <div>
                                            <?php if (!empty($product['original_price'])): ?>
                                                <span class="text-xs text-gray-400 line-through"><?= formatPrice($product['original_price']) ?></span>
                                            <?php endif; ?>
                                            <span class="block text-lg font-bold text-orange-600"><?= formatPrice($product['price']) ?></span>
                                        </div>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button onclick="addToCart(<?= $product['id'] ?>)" class="bg-orange-500 text-white hover:bg-orange-600 px-3 py-1.5 text-sm rounded transition-colors">
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
