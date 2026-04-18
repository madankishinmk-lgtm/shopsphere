<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function formatPrice($amount) {
    return '$' . number_format((float)$amount, 2, '.', ',');
}

function sanitize($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type][] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $messages = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }
    return [];
}

function displayFlashMessages() {
    $types = ['success' => 'bg-green-100 border-green-400 text-green-700',
              'error'   => 'bg-red-100 border-red-400 text-red-700',
              'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700'];

    $output = '';
    foreach ($types as $type => $classes) {
        $messages = getFlash($type);
        foreach ($messages as $message) {
            $output .= "<div class='border px-4 py-3 rounded relative mb-4 {$classes}' role='alert'>
                            <span class='block sm:inline'>" . sanitize($message) . "</span>
                        </div>";
        }
    }
    return $output;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function getProductImage($product) {
    
    static $imgBase = null;
    if ($imgBase === null) {
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $docRoot     = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $imgBase     = '/' . ltrim(str_replace($docRoot, '', $projectRoot), '/') . '/images/products/';
    }

    $imageUrl = $product['image_url'] ?? '';
    if (!empty($imageUrl) && $imageUrl !== 'placeholder.jpg' && !str_starts_with($imageUrl, 'placeholder')) {
        return str_starts_with($imageUrl, 'http')
            ? $imageUrl
            : $imgBase . htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
    }

    $name = strtolower($product['name'] ?? '');

    
    if (str_contains($name, 'noise cancelling') || str_contains($name, 'noise-cancelling'))
        return 'https://loremflickr.com/800/800/headphones?lock=21';
    if (str_contains($name, 'wireless earbud') || str_contains($name, 'earbuds') || str_contains($name, 'earphone'))
        return 'https://loremflickr.com/800/800/earphones?lock=14';
    if (str_contains($name, 'headphone'))
        return 'https://loremflickr.com/800/800/headphones?lock=7';
    if (str_contains($name, 'gaming laptop'))
        return 'https://loremflickr.com/800/800/laptop?lock=5';
    if (str_contains($name, 'laptop'))
        return 'https://loremflickr.com/800/800/laptop?lock=11';
    if (str_contains($name, 'smart tv') || str_contains($name, '4k tv') || str_contains($name, 'television'))
        return 'https://loremflickr.com/800/800/television?lock=3';
    if (str_contains($name, 'wireless mouse') || (str_contains($name, 'mouse') && !str_contains($name, 'house')))
        return 'https://loremflickr.com/800/800/mouse?lock=8';
    if (str_contains($name, 'mechanical keyboard') || str_contains($name, 'keyboard'))
        return 'https://loremflickr.com/800/800/keyboard?lock=6';
    if (str_contains($name, 'smartwatch') || str_contains($name, 'smart watch'))
        return 'https://loremflickr.com/800/800/smartwatch?lock=9';
    if (str_contains($name, 'digital camera') || str_contains($name, 'dslr') || str_contains($name, 'camera'))
        return 'https://loremflickr.com/800/800/camera?lock=4';
    if (str_contains($name, 'bluetooth speaker') || str_contains($name, 'speaker'))
        return 'https://loremflickr.com/800/800/speaker?lock=12';
    if (str_contains($name, 'tablet') || str_contains($name, 'ipad'))
        return 'https://loremflickr.com/800/800/tablet?lock=15';
    if (str_contains($name, 'smartphone') || str_contains($name, 'iphone') || str_contains($name, 'mobile phone'))
        return 'https://loremflickr.com/800/800/smartphone?lock=2';

    
    if (str_contains($name, 'hoodie') || str_contains($name, 'sweatshirt'))
        return 'https://loremflickr.com/800/800/hoodie?lock=10';
    if (str_contains($name, 'jacket') || str_contains($name, 'coat'))
        return 'https://loremflickr.com/800/800/jacket?lock=7';
    if (str_contains($name, 'dress'))
        return 'https://loremflickr.com/800/800/dress?lock=5';
    if (str_contains($name, 'denim') || str_contains($name, 'jeans'))
        return 'https://loremflickr.com/800/800/jeans?lock=3';
    if (str_contains($name, 'sneaker') || str_contains($name, 'shoe') || str_contains($name, 'boot'))
        return 'https://loremflickr.com/800/800/sneakers?lock=8';
    if (str_contains($name, 't-shirt') || str_contains($name, 'tshirt'))
        return 'https://loremflickr.com/800/800/tshirt?lock=4';
    if (str_contains($name, 'cap') || str_contains($name, 'hat'))
        return 'https://loremflickr.com/800/800/cap?lock=6';
    if (str_contains($name, 'wallet'))
        return 'https://loremflickr.com/800/800/wallet?lock=9';

    
    if (str_contains($name, 'gatsby'))
        return 'https://covers.openlibrary.org/b/isbn/9780743273565-L.jpg';
    if (str_contains($name, '1984') || str_contains($name, 'orwell'))
        return 'https://covers.openlibrary.org/b/isbn/9780451524935-L.jpg';
    if (str_contains($name, 'sapiens'))
        return 'https://covers.openlibrary.org/b/isbn/9780062316110-L.jpg';
    if (str_contains($name, 'atomic habits'))
        return 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg';
    if (str_contains($name, 'midnight library'))
        return 'https://covers.openlibrary.org/b/isbn/9780525559474-L.jpg';
    if (str_contains($name, 'dune'))
        return 'https://covers.openlibrary.org/b/isbn/9780441013593-L.jpg';
    if (str_contains($name, 'book') || str_contains($name, 'novel') || str_contains($name, 'biography'))
        return 'https://loremflickr.com/800/800/book?lock=10';

    
    if (str_contains($name, 'sofa') || str_contains($name, 'couch'))
        return 'https://loremflickr.com/800/800/sofa?lock=8';
    if (str_contains($name, 'coffee table'))
        return 'https://loremflickr.com/800/800/furniture?lock=5';
    if (str_contains($name, 'table'))
        return 'https://loremflickr.com/800/800/furniture?lock=7';
    if (str_contains($name, 'lamp'))
        return 'https://loremflickr.com/800/800/lamp?lock=3';
    if (str_contains($name, 'vase') || str_contains($name, 'ceramic'))
        return 'https://loremflickr.com/800/800/vase?lock=11';
    if (str_contains($name, 'pillow') || str_contains($name, 'cushion'))
        return 'https://loremflickr.com/800/800/pillow?lock=6';
    if (str_contains($name, 'diffuser') || str_contains($name, 'aromatherapy'))
        return 'https://loremflickr.com/800/800/candle?lock=4';
    if (str_contains($name, 'kettle'))
        return 'https://loremflickr.com/800/800/kettle?lock=5';
    if (str_contains($name, 'coffee maker') || str_contains($name, 'coffee'))
        return 'https://loremflickr.com/800/800/coffee?lock=2';

    
    if (str_contains($name, 'yoga mat') || str_contains($name, 'yoga'))
        return 'https://loremflickr.com/800/800/yoga?lock=7';
    if (str_contains($name, 'dumbbell') || str_contains($name, 'weight'))
        return 'https://loremflickr.com/800/800/gym?lock=5';
    if (str_contains($name, 'tennis') || str_contains($name, 'racket'))
        return 'https://loremflickr.com/800/800/tennis?lock=3';
    if (str_contains($name, 'basketball'))
        return 'https://loremflickr.com/800/800/basketball?lock=3';
    if (str_contains($name, 'resistance band') || str_contains($name, 'resistance bands'))
        return 'https://loremflickr.com/800/800/fitness?lock=10';
    if (str_contains($name, 'bicycle') || str_contains($name, 'cycling') || str_contains($name, 'bike'))
        return 'https://loremflickr.com/800/800/bicycle?lock=4';
    if (str_contains($name, 'goggle') || str_contains($name, 'swimming'))
        return 'https://loremflickr.com/800/800/swimming?lock=8';
    if (str_contains($name, 'jump rope') || str_contains($name, 'skipping rope') || str_contains($name, 'jump'))
        return 'https://loremflickr.com/800/800/jumping?lock=11';
    if (str_contains($name, 'football') || str_contains($name, 'soccer'))
        return 'https://loremflickr.com/800/800/football?lock=2';

    
    $category = strtolower($product['category_name'] ?? '');
    if (str_contains($category, 'electronic'))  return 'https://loremflickr.com/800/800/electronics?lock=1';
    if (str_contains($category, 'cloth') || str_contains($category, 'fashion')) return 'https://loremflickr.com/800/800/fashion?lock=1';
    if (str_contains($category, 'book'))        return 'https://loremflickr.com/800/800/book?lock=1';
    if (str_contains($category, 'home') || str_contains($category, 'living')) return 'https://loremflickr.com/800/800/home?lock=1';
    if (str_contains($category, 'sport'))       return 'https://loremflickr.com/800/800/sport?lock=1';

    return 'https://loremflickr.com/800/800/shopping?lock=1';
}

