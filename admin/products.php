<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(); // Hard block — redirects non-admins

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        try {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_POST['delete_id']]);
            setFlash('success', 'Product deleted successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Cannot delete — product is referenced in existing orders.');
        }
    }
    redirect('products.php');
}

// ── ADD PRODUCT ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $name     = trim($_POST['name'] ?? '');
        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $catId    = (int)($_POST['category_id'] ?? 0);
        $desc     = trim($_POST['description'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $stock    = (int)($_POST['stock'] ?? 0);
        $isNew    = isset($_POST['is_new']) ? 1 : 0;
        $isSale   = isset($_POST['is_sale']) ? 1 : 0;

        // Check slug uniqueness
        $exists = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $exists->execute([$slug]);
        if ($exists->fetch()) {
            $slug .= '-' . time();
        }

        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price)
            VALUES (?, ?, ?, ?, ?, ?, 'placeholder.jpg', ?, ?, ?)
        ");
        $stmt->execute([$catId, $name, $slug, $desc, $price, $stock, $isNew, $isSale, $origPrice]);
        setFlash('success', "Product \"$name\" added successfully.");
        redirect('products.php');
    }
}

// ── INLINE EDIT (quick update) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_edit') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $id       = (int)$_POST['product_id'];
        $price    = (float)$_POST['price'];
        $stock    = (int)$_POST['stock'];
        $isNew    = isset($_POST['is_new']) ? 1 : 0;
        $isSale   = isset($_POST['is_sale']) ? 1 : 0;
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $pdo->prepare("
            UPDATE products SET price=?, stock=?, is_new=?, is_sale=?, original_price=? WHERE id=?
        ")->execute([$price, $stock, $isNew, $isSale, $origPrice, $id]);
        setFlash('success', 'Product updated.');
        redirect('products.php');
    }
}

// ── FETCH ─────────────────────────────────────────────────────────────────────
$search    = $_GET['search'] ?? '';
$filterCat = $_GET['cat'] ?? '';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 15;
$offset    = ($page - 1) * $perPage;

$where       = ['1=1'];
$paramValues = [];   // named param map used by BOTH queries

if ($search)    { $where[] = 'p.name LIKE :search'; $paramValues[':search'] = "%$search%"; }
if ($filterCat) { $where[] = 'c.slug = :cat';       $paramValues[':cat']    = $filterCat;   }
$whereStr = implode(' AND ', $where);

// COUNT query (only filter params, no pagination)
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id=c.id WHERE $whereStr");
$stmtCount->execute($paramValues);
$totalProducts = $stmtCount->fetchColumn();
$totalPages    = max(1, ceil($totalProducts / $perPage));

// Main query — add :limit/:offset to named params
$stmtMain = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p JOIN categories c ON p.category_id = c.id
    WHERE $whereStr
    ORDER BY p.id DESC
    LIMIT :limit OFFSET :offset
");
foreach ($paramValues as $key => $val) {
    $stmtMain->bindValue($key, $val);
}
$stmtMain->bindValue(':limit',  (int)$perPage, PDO::PARAM_INT);
$stmtMain->bindValue(':offset', (int)$offset,  PDO::PARAM_INT);
$stmtMain->execute();
$products = $stmtMain->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-grow bg-gray-50 py-8 pb-10">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">🛍 Manage Products</h1>
            <p class="text-sm text-gray-500 mt-1"><?= $totalProducts ?> products total</p>
        </div>
        <button onclick="document.getElementById('add-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow transition-colors text-sm">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Product
        </button>
    </div>

    <!-- Filter bar -->
    <form method="GET" class="flex flex-wrap gap-3 mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Search products..."
               class="flex-1 min-w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <select name="cat" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= sanitize($c['slug']) ?>" <?= $filterCat === $c['slug'] ? 'selected' : '' ?>>
                    <?= sanitize($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">Filter</button>
        <?php if ($search || $filterCat): ?>
            <a href="products.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Product Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Badges</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50 transition-colors" id="row-<?= $product['id'] ?>">
                        <!-- Product name + image -->
                        <td class="py-3 pl-4 pr-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border">
                                    <img src="<?= getProductImage($product) ?>" alt="<?= sanitize($product['name']) ?>"
                                         class="w-full h-full object-cover" loading="lazy"
                                         onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100&q=60'">
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 max-w-xs truncate"><?= sanitize($product['name']) ?></p>
                                    <p class="text-xs text-gray-400 font-mono">/<?= sanitize($product['slug']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                <?= sanitize($product['category_name']) ?>
                            </span>
                        </td>
                        <td class="px-3 py-3 text-sm font-semibold text-gray-900">
                            <?= formatPrice($product['price']) ?>
                            <?php if (!empty($product['original_price'])): ?>
                                <span class="text-xs text-gray-400 line-through font-normal ml-1"><?= formatPrice($product['original_price']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3 text-sm">
                            <?php if ($product['stock'] <= 0): ?>
                                <span class="text-red-600 font-bold">Out of Stock</span>
                            <?php elseif ($product['stock'] < 10): ?>
                                <span class="text-orange-500 font-semibold"><?= $product['stock'] ?> <span class="text-xs font-normal">(low)</span></span>
                            <?php else: ?>
                                <span class="text-green-600 font-medium"><?= $product['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3 text-sm">
                            <div class="flex gap-1">
                                <?php if (!empty($product['is_new'])): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-semibold">NEW</span>
                                <?php endif; ?>
                                <?php if (!empty($product['is_sale'])): ?>
                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full font-semibold">SALE</span>
                                <?php endif; ?>
                                <?php if (empty($product['is_new']) && empty($product['is_sale'])): ?>
                                    <span class="text-gray-300 text-xs">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="openEdit(<?= htmlspecialchars(json_encode($product), ENT_QUOTES) ?>)"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                    ✏ Edit
                                </button>
                                <form method="POST" onsubmit="return confirm('Delete \"<?= sanitize($product['name']) ?>\"? This cannot be undone.');" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        🗑 Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="py-16 text-center text-gray-400">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($filterCat) ?>"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">← Prev</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&cat=<?= urlencode($filterCat) ?>"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>

<!-- ══════════ ADD PRODUCT MODAL ══════════ -->
<div id="add-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm">
  <div class="flex min-h-full items-center justify-center p-4 py-8">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Add New Product</h2>
            <button onclick="document.getElementById('add-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-5">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Wireless Keyboard"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Price ($) *</label>
                    <input type="number" name="price" step="0.01" min="0" required placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Original Price ($) <span class="text-gray-400 font-normal">(sale only)</span></label>
                    <input type="number" name="original_price" step="0.01" min="0" placeholder="Leave blank if not on sale"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stock Quantity *</label>
                    <input type="number" name="stock" min="0" required placeholder="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Short product description..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"></textarea>
                </div>
                <div class="sm:col-span-2 flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_new" class="h-4 w-4 text-indigo-600 rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Mark as <span class="text-green-600 font-bold">NEW</span></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_sale" class="h-4 w-4 text-orange-500 rounded border-gray-300">
                        <span class="text-sm font-medium text-gray-700">Mark as <span class="text-orange-500 font-bold">SALE</span></span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow transition-colors">
                    Add Product
                </button>
            </div>
        </form>
    </div>
  </div>
</div>

<!-- ══════════ QUICK EDIT MODAL ══════════ -->
<div id="edit-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm">
  <div class="flex min-h-full items-center justify-center p-4 py-8">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Quick Edit Product</h2>
            <button onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4" id="edit-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="quick_edit">
            <input type="hidden" name="product_id" id="edit-product-id">

            <div id="edit-product-preview" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-2">
                <img id="edit-img" src="" class="w-14 h-14 rounded-xl object-cover border bg-gray-100">
                <div>
                    <p id="edit-name-label" class="font-semibold text-gray-900 text-sm"></p>
                    <p id="edit-cat-label" class="text-xs text-gray-400"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Price ($)</label>
                    <input type="number" name="price" id="edit-price" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Original Price ($)</label>
                    <input type="number" name="original_price" id="edit-orig-price" step="0.01" min="0" placeholder="none"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stock</label>
                    <input type="number" name="stock" id="edit-stock" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-6 pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_new" id="edit-is-new" class="h-4 w-4 text-indigo-600 rounded">
                    <span class="text-sm font-medium text-gray-700">Mark <span class="text-green-600 font-bold">NEW</span></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_sale" id="edit-is-sale" class="h-4 w-4 text-orange-500 rounded">
                    <span class="text-sm font-medium text-gray-700">Mark <span class="text-orange-500 font-bold">SALE</span></span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <a id="edit-full-link" href="#"
                   class="px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors">
                    Full Edit →
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
function openEdit(product) {
    document.getElementById('edit-product-id').value = product.id;
    document.getElementById('edit-price').value = product.price;
    document.getElementById('edit-orig-price').value = product.original_price || '';
    document.getElementById('edit-stock').value = product.stock;
    document.getElementById('edit-is-new').checked = product.is_new == 1;
    document.getElementById('edit-is-sale').checked = product.is_sale == 1;
    document.getElementById('edit-name-label').textContent = product.name;
    document.getElementById('edit-cat-label').textContent = product.category_name;
    document.getElementById('edit-full-link').href = 'edit_product.php?id=' + product.id + '&return_page=<?= $page ?>';

    // Try to get the image from the existing row thumbnail
    const rowImg = document.querySelector('#row-' + product.id + ' img');
    document.getElementById('edit-img').src = rowImg ? rowImg.src : '';

    document.getElementById('edit-modal').classList.remove('hidden');
}

// Close modals when clicking the backdrop or the centering wrapper (outside the card)
['add-modal','edit-modal'].forEach(id => {
    const overlay = document.getElementById(id);
    overlay.addEventListener('click', function(e) {
        // Close if click lands on the backdrop or the flex centering wrapper
        if (e.target === overlay || e.target === overlay.firstElementChild) {
            overlay.classList.add('hidden');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
