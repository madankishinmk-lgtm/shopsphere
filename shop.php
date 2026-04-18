<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Filtering setup
$categorySlug = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$minPrice = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;

// Fetch Categories for Sidebar
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$allCategories = $stmt->fetchAll();

// Build Base Query
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($categorySlug) {
    $query .= " AND c.slug = ?";
    $params[] = $categorySlug;
}
if ($search) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}
if ($minPrice > 0) {
    $query .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice < 10000) {
    $query .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Get Total for Pagination
$countQuery = str_replace("SELECT p.*, c.name as category_name, c.slug as category_slug", "SELECT COUNT(*)", $query);
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$totalProducts = $stmtCount->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Final Fetch Query
$query .= " ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Helper for pagination links
function getPaginationUrl($p) {
    return '?' . http_build_query(array_merge($_GET, ['page' => $p]));
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Filter (Desktop) -->
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 tracking-wide">Filters</h3>
                
                <form method="GET" action="shop.php" class="space-y-6">
                    
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="search" id="search" value="<?= sanitize($search) ?>" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 border py-2 px-3 rounded-md" placeholder="Keywords...">
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input id="cat-all" name="category" value="" type="radio" <?= empty($categorySlug) ? 'checked' : '' ?> class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="cat-all" class="ml-3 text-sm text-gray-600">All Categories</label>
                            </div>
                            <?php foreach ($allCategories as $cat): ?>
                                <div class="flex items-center">
                                    <input id="cat-<?= $cat['id'] ?>" name="category" value="<?= sanitize($cat['slug']) ?>" type="radio" <?= $categorySlug === $cat['slug'] ? 'checked' : '' ?> class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="cat-<?= $cat['id'] ?>" class="ml-3 text-sm text-gray-600"><?= sanitize($cat['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="min_price" value="<?= $minPrice > 0 ? $minPrice : '' ?>" placeholder="Min" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 border py-2 px-3 rounded-md">
                            <span class="text-gray-500">-</span>
                            <input type="number" name="max_price" value="<?= $maxPrice < 10000 ? $maxPrice : '' ?>" placeholder="Max" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 border py-2 px-3 rounded-md">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 border border-transparent rounded-md py-2 px-4 flex items-center justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                    <?php if(!empty($_GET)): ?>
                         <a href="shop.php" class="w-full mt-2 bg-white border border-gray-300 rounded-md py-2 px-4 flex items-center justify-center text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                            Clear Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 mb-6">
                <?php 
                    if ($categorySlug) echo 'Category: ' . sanitize(ucwords(str_replace('-', ' ', $categorySlug)));
                    else if ($search) echo 'Search: "' . sanitize($search) . '"';
                    else echo "All Products";
                ?>
                <span class="text-sm font-normal text-gray-500 ml-2">(<?= $totalProducts ?> results)</span>
            </h1>

            <?php if (empty($products)): ?>
                <div class="text-center py-24 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter options.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:gap-x-8">
                    <?php foreach ($products as $product): ?>
                        <div class="group bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden">
                            <!-- Product Image -->
                            <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="block relative overflow-hidden">
                                <?php if ($product['stock'] <= 0): ?>
                                    <span class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">Out of Stock</span>
                                <?php elseif (!empty($product['is_sale']) && $product['is_sale']): ?>
                                    <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">SALE</span>
                                <?php elseif (!empty($product['is_new']) && $product['is_new']): ?>
                                    <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow z-10">NEW</span>
                                <?php endif; ?>
                                <span class="absolute top-2 left-2 bg-white text-indigo-700 text-xs font-semibold px-2 py-1 rounded-full shadow z-10"><?= sanitize($product['category_name']) ?></span>
                                <img src="<?= getProductImage($product) ?>"
                                     alt="<?= sanitize($product['name']) ?>"
                                     class="w-full h-56 object-cover object-center group-hover:scale-105 transition-transform duration-500">
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="admin/edit_product.php?id=<?= $product['id'] ?>"
                                   class="absolute bottom-2 right-2 z-20 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-lg transition-colors opacity-0 group-hover:opacity-100"
                                   title="Admin: Edit product">
                                    ✏ Edit
                                </a>
                            <?php endif; ?>

                            <!-- Product Info -->
                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="text-base font-semibold text-gray-900 mb-1">
                                    <a href="product.php?slug=<?= urlencode($product['slug']) ?>" class="hover:text-indigo-600 transition-colors">
                                        <?= sanitize($product['name']) ?>
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-500 mb-3 flex-1 line-clamp-2"><?= sanitize($product['description']) ?></p>

                                <div class="flex items-center justify-between mt-auto">
                                    <div>
                                        <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                            <span class="text-xs text-gray-400 line-through mr-1"><?= formatPrice($product['original_price']) ?></span>
                                        <?php endif; ?>
                                        <span class="text-lg font-bold text-indigo-600"><?= formatPrice($product['price']) ?></span>
                                    </div>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button onclick="addToCart(<?= $product['id'] ?>)" class="bg-indigo-600 text-white hover:bg-indigo-700 px-3 py-1.5 text-sm rounded shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-red-500 font-medium">Unavailable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-12 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium"><?= $offset + 1 ?></span> to <span class="font-medium"><?= min($offset + $perPage, $totalProducts) ?></span> of <span class="font-medium"><?= $totalProducts ?></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="<?= getPaginationUrl($page - 1) ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                        <a href="<?= getPaginationUrl($i) ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $page ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' ?>">
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="<?= getPaginationUrl($page + 1) ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
