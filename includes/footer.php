    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                <div class="col-span-2 md:col-span-1">
                    <span class="text-xl font-extrabold text-indigo-400">ShopSphere</span>
                    <p class="mt-2 text-xs text-gray-400 leading-relaxed">Premium products for your everyday life, delivered with care.</p>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-3">Shop</h3>
                    <ul class="space-y-1.5 text-xs text-gray-400">
                        <li><a href="<?= $baseUrl ?>shop.php" class="hover:text-white transition-colors">All Products</a></li>
                        <li><a href="<?= $baseUrl ?>new-arrivals.php" class="hover:text-white transition-colors">New Arrivals</a></li>
                        <li><a href="<?= $baseUrl ?>sale.php" class="hover:text-white transition-colors">Sale &amp; Deals</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-3">Categories</h3>
                    <ul class="space-y-1.5 text-xs text-gray-400">
                        <li><a href="<?= $baseUrl ?>shop.php?category=electronics" class="hover:text-white transition-colors">Electronics</a></li>
                        <li><a href="<?= $baseUrl ?>shop.php?category=clothing" class="hover:text-white transition-colors">Clothing</a></li>
                        <li><a href="<?= $baseUrl ?>shop.php?category=books" class="hover:text-white transition-colors">Books</a></li>
                        <li><a href="<?= $baseUrl ?>shop.php?category=home-living" class="hover:text-white transition-colors">Home &amp; Living</a></li>
                        <li><a href="<?= $baseUrl ?>shop.php?category=sports" class="hover:text-white transition-colors">Sports</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-300 uppercase tracking-wider mb-3">Account</h3>
                    <ul class="space-y-1.5 text-xs text-gray-400">
                        <li><a href="<?= $baseUrl ?>login.php" class="hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="<?= $baseUrl ?>register.php" class="hover:text-white transition-colors">Register</a></li>
                        <li><a href="<?= $baseUrl ?>orders.php" class="hover:text-white transition-colors">My Orders</a></li>
                        <li><a href="<?= $baseUrl ?>profile.php" class="hover:text-white transition-colors">My Profile</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">&copy; 2026 ShopSphere, Inc. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?= $baseUrl ?>assets/js/main.js"></script>
    <script src="<?= $baseUrl ?>assets/js/cart.js"></script>
</body>
</html>
