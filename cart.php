<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];
$cartItems = [];
$subtotal = 0;

$stmt = $pdo->prepare("
    SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price, p.stock, p.image_url, p.slug
    FROM cart_items ci
    JOIN carts c ON ci.cart_id = c.id
    JOIN products p ON ci.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal > 50 ? 0 : 10; 
$total = $subtotal + $shipping;

if (empty($cartItems)) {
    $shipping = 0;
    $total = 0;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gray-50 flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 mb-8">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white p-12 text-center rounded-lg shadow-sm border border-gray-200">
                <svg class="mx-auto h-24 w-24 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
                
                
                <div class="lg:col-span-8 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <ul role="list" class="divide-y divide-gray-200" id="cart-items-container">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="p-6 flex py-6" id="cart-item-<?= $item['product_id'] ?>">
                                
                                <div class="flex-shrink-0 w-24 h-24 border border-gray-200 rounded-md overflow-hidden flex items-center justify-center bg-gray-50">
                                    <img src="<?= getProductImage($item) ?>" 
                                         alt="<?= sanitize($item['name']) ?>"
                                         class="w-full h-full object-cover object-center">
                                </div>

                                
                                <div class="ml-4 flex-1 flex flex-col justify-between">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <a href="product.php?slug=<?= sanitize($item['slug']) ?>" class="hover:text-indigo-600"><?= sanitize($item['name']) ?></a>
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500 font-medium"><?= formatPrice($item['price']) ?></p>
                                        </div>
                                        <p class="ml-4 text-sm font-medium text-gray-900" id="subtotal-<?= $item['product_id'] ?>"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                                    </div>
                                    
                                    <div class="flex flex-1 items-end justify-between text-sm">
                                        <div class="flex items-center space-x-3 border border-gray-300 rounded-md bg-white">
                                            
                                            <button type="button" onclick="updateCartQuantity(<?= $item['product_id'] ?>, -1, <?= $item['price'] ?>)" class="px-3 py-1 text-gray-600 hover:text-indigo-600 focus:outline-none transition-colors border-r border-gray-300">&minus;</button>
                                            <span class="font-medium px-2" id="qty-<?= $item['product_id'] ?>"><?= $item['quantity'] ?></span>
                                            <button type="button" onclick="updateCartQuantity(<?= $item['product_id'] ?>, 1, <?= $item['price'] ?>)" class="px-3 py-1 text-gray-600 hover:text-indigo-600 focus:outline-none transition-colors border-l border-gray-300 text-lg">&plus;</button>
                                        </div>

                                        <div class="flex">
                                            <button type="button" onclick="removeFromCart(<?= $item['product_id'] ?>)" class="font-medium text-red-500 hover:text-red-700 transition-colors flex items-center space-x-1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span>Remove</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                
                <div class="lg:col-span-4 mt-8 lg:mt-0">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-medium text-gray-900 mb-6 border-b pb-4">Order summary</h2>
                        
                        <dl class="space-y-4 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <dt>Subtotal</dt>
                                <dd class="font-medium text-gray-900" id="summary-subtotal"><?= formatPrice($subtotal) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Shipping estimate</dt>
                                <dd class="font-medium text-gray-900" id="summary-shipping"><?= $shipping === 0 ? 'Free' : formatPrice($shipping) ?></dd>
                            </div>
                            
                            
                            <?php if ($shipping > 0): ?>
                                <div class="bg-indigo-50 text-indigo-700 px-3 py-2 rounded text-xs text-center border border-indigo-100" id="shipping-alert">
                                    Spend $<?= number_format(50 - $subtotal, 2) ?> more to get free shipping!
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between border-t border-gray-200 pt-4">
                                <dt class="text-base font-medium text-gray-900">Total order</dt>
                                <dd class="text-xl font-bold text-indigo-600" id="summary-total"><?= formatPrice($total) ?></dd>
                            </div>
                        </dl>

                        <div class="mt-6">
                            <a href="checkout.php" class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-indigo-500 text-center block transition-colors">
                                Proceed to Checkout
                            </a>
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-500">
                                or <a href="shop.php" class="text-indigo-600 font-medium hover:text-indigo-500">Continue Shopping <span aria-hidden="true">&rarr;</span></a>
                            </p>
                        </div>
                    </div>
                </div>
                
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Inline script or put in assets/js/cart.js
// Simplified format for currency
const formatMoney = (amount) => {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
};

let currentSubtotal = <?= $subtotal ?>;

function recomputeTotals() {
    let subtotal = 0;
    // Iterate over items based on DOM elements because quantity updates
    document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
        let val = parseFloat(el.innerText.replace('$', '').replace(',', ''));
        if(!isNaN(val)) subtotal += val;
    });
    
    let shipping = subtotal > 50 ? 0 : (subtotal > 0 ? 10 : 0);
    let total = subtotal + shipping;
    
    document.getElementById('summary-subtotal').innerText = formatMoney(subtotal);
    document.getElementById('summary-shipping').innerText = shipping === 0 ? 'Free' : formatMoney(shipping);
    document.getElementById('summary-total').innerText = formatMoney(total);

    // Refresh page if cart empty
    if(subtotal === 0) location.reload();
}

async function updateCartQuantity(productId, change, unitPrice) {
    const qtySpan = document.getElementById(`qty-${productId}`);
    let currentQty = parseInt(qtySpan.innerText);
    let newQty = currentQty + change;
    
    if(newQty <= 0) {
        removeFromCart(productId);
        return;
    }

    // Call server API
    try {
        const response = await fetch('cart_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'update', product_id: productId, quantity: newQty})
        });
        const result = await response.json();
        
        if(result.success) {
            qtySpan.innerText = newQty;
            document.getElementById(`subtotal-${productId}`).innerText = formatMoney(newQty * unitPrice);
            recomputeTotals();
        } else {
            alert(result.message);
        }
    } catch(err) {
        console.error(err);
        alert('Failed to update cart');
    }
}

async function removeFromCart(productId) {
    if(confirm('Are you sure you want to remove this item?')) {
        try {
            const response = await fetch('cart_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'remove', product_id: productId})
            });
            const result = await response.json();
            
            if(result.success) {
                document.getElementById(`cart-item-${productId}`).remove();
                recomputeTotals();
            } else {
                alert(result.message);
            }
        } catch(err) {
            console.error(err);
        }
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
