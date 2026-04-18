// cart.js - Handles global 'Add to Cart' functionality from the Shop page

function createToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-5 right-5 bg-indigo-600 text-white px-6 py-3 rounded shadow-lg z-50 transform transition-all duration-300 translate-y-0 opacity-100';
    toast.innerText = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-10', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

async function addToCart(productId) {
    // If the cart doesn't exist via session, the PHP endpoint returns 'Please log in'
    // We determine base URL to make sure it resolves properly from /admin or root
    const baseUrl = window.location.pathname.includes('/admin/') ? '../' : '';
    
    try {
        const response = await fetch(baseUrl + 'cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1
            })
        });
        
        const result = await response.json();
        if (result.success) {
            createToast('Added to cart!');
            // Optional: update a cart badge counter here
        } else {
            alert(result.message);
            if (result.message.includes('log in')) {
                window.location.href = baseUrl + 'login.php';
            }
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Failed to connect to the server.');
    }
}
