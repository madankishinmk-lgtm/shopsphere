<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$isEditing = $id !== null;
$returnPage = max(1, (int)($_GET['return_page'] ?? 1));
$errors = [];

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$product = [
    'name' => '', 'slug' => '', 'category_id' => '', 'description' => '',
    'price' => '', 'stock' => '', 'image_url' => '',
    'original_price' => '', 'is_new' => 0, 'is_sale' => 0
];

if ($isEditing) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        setFlash('error', 'Product not found.');
        redirect('products.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)));
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $origPrice   = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $isNew       = isset($_POST['is_new']) ? 1 : 0;
        $isSale      = isset($_POST['is_sale']) ? 1 : 0;
        $imageUrl    = $product['image_url'];

        if (empty($name))       $errors[] = "Product name is required.";
        if ($categoryId <= 0)   $errors[] = "Category is required.";
        if ($price <= 0)        $errors[] = "Price must be greater than 0.";

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fi, $_FILES['image']['tmp_name']);
            finfo_close($fi);
            if (!in_array($mime, $allowed)) {
                $errors[] = "Invalid image format. Only JPG, PNG and WebP are allowed.";
            } else {
                $ext = match($mime) { 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp' };
                $uploadDir = __DIR__ . '/../images/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $newFilename = bin2hex(random_bytes(16)) . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                    $imageUrl = $newFilename;
                } else {
                    $errors[] = "Failed to move uploaded file.";
                }
            }
        }

        if (empty($errors)) {
            try {
                if ($isEditing) {
                    $pdo->prepare("
                        UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, stock=?,
                        image_url=?, original_price=?, is_new=?, is_sale=? WHERE id=?
                    ")->execute([$name, $slug, $categoryId, $description, $price, $stock,
                                 $imageUrl, $origPrice, $isNew, $isSale, $id]);
                    setFlash('success', 'Product updated successfully.');
                } else {
                    if (empty($slug)) $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                    $pdo->prepare("
                        INSERT INTO products (name, slug, category_id, description, price, stock, image_url, original_price, is_new, is_sale)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ")->execute([$name, $slug, $categoryId, $description, $price, $stock,
                                 $imageUrl ?: 'placeholder.jpg', $origPrice, $isNew, $isSale]);
                    setFlash('success', 'Product added successfully.');
                }
                redirect('products.php?page=' . $returnPage);
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-grow bg-gray-50 py-10 pb-12">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="products.php" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                ← Back to Products
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900">
                <?= $isEditing ? '✏ Edit Product' : '➕ Add New Product' ?>
            </h1>
        </div>
        <?php if ($isEditing): ?>
            <a href="../product.php?slug=<?= urlencode($product['slug']) ?>" target="_blank"
               class="text-sm text-gray-500 hover:text-indigo-600 border border-gray-200 px-3 py-1.5 rounded-lg">
                View on Site ↗
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                <?php foreach ($errors as $err): ?><li><?= sanitize($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="edit_product.php<?= $isEditing ? "?id=$id&return_page=$returnPage" : "" ?>" method="POST"
          enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <?= csrfField() ?>

        <div class="px-6 py-8 space-y-6">

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" id="name-input" required
                           value="<?= sanitize($_POST['name'] ?? $product['name']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                           placeholder="e.g. Wireless Keyboard">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">URL Slug</label>
                    <input type="text" name="slug" id="slug-input"
                           value="<?= sanitize($_POST['slug'] ?? $product['slug']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                           placeholder="auto-generated">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to auto-generate from name.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <option value="">Select a category</option>
                        <?php $selectedCat = $_POST['category_id'] ?? $product['category_id']; ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCat == $cat['id'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Price ($) *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           value="<?= sanitize($_POST['price'] ?? $product['price']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Original Price ($) <span class="text-gray-400 font-normal">(for sale)</span></label>
                    <input type="number" name="original_price" step="0.01" min="0"
                           value="<?= sanitize($_POST['original_price'] ?? $product['original_price']) ?>"
                           placeholder="Leave blank if not on sale"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stock Quantity</label>
                    <input type="number" name="stock" min="0"
                           value="<?= sanitize($_POST['stock'] ?? $product['stock']) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-8 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="is_new" class="h-5 w-5 text-green-500 rounded border-gray-300"
                           <?= ($_POST['is_new'] ?? $product['is_new']) ? 'checked' : '' ?>>
                    <div>
                        <span class="block text-sm font-semibold text-gray-700">Mark as <span class="text-green-600">NEW</span></span>
                        <span class="text-xs text-gray-400">Shows in New Arrivals page</span>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" name="is_sale" class="h-5 w-5 text-orange-500 rounded border-gray-300"
                           <?= ($_POST['is_sale'] ?? $product['is_sale']) ? 'checked' : '' ?>>
                    <div>
                        <span class="block text-sm font-semibold text-gray-700">Mark as <span class="text-orange-500">SALE</span></span>
                        <span class="text-xs text-gray-400">Shows in Sale page with discount badge</span>
                    </div>
                </label>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
                          placeholder="Short description of the product..."><?= sanitize($_POST['description'] ?? $product['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
                <?php if ($isEditing): ?>
                    <div class="mb-3 flex items-center gap-4">
                        <img src="<?= getProductImage($product) ?>" alt="<?= sanitize($product['name']) ?>"
                             class="w-24 h-24 object-cover rounded-xl border shadow-sm" id="img-preview">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Current image</p>
                            <p class="text-xs text-gray-400"><?= sanitize($product['image_url']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="border-2 border-dashed border-gray-300 rounded-xl px-6 py-8 text-center hover:border-indigo-400 transition-colors">
                    <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <label for="image" class="cursor-pointer text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Click to upload image
                        <input id="image" name="image" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(event)">
                    </label>
                    <p class="text-xs text-gray-400 mt-2">PNG, JPG, WEBP — max 5MB</p>
                    <p class="text-xs text-gray-400 mt-1"><?= $isEditing ? 'Leave empty to keep current image' : '' ?></p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 px-6 py-4 bg-gray-50">
            <a href="products.php?page=<?= $returnPage ?>" class="text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-2.5 rounded-xl shadow transition-colors text-sm">
                <?= $isEditing ? 'Save Changes' : 'Add Product' ?>
            </button>
        </div>
    </form>
</div>
</div>

<script>

document.getElementById('name-input')?.addEventListener('input', function() {
    const slugInput = document.getElementById('slug-input');
    if (!slugInput.value || slugInput.dataset.dirty !== 'true') {
        slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});
document.getElementById('slug-input')?.addEventListener('input', function() {
    this.dataset.dirty = 'true';
});

function previewImage(event) {
    const preview = document.getElementById('img-preview');
    if (preview && event.target.files[0]) {
        preview.src = URL.createObjectURL(event.target.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
