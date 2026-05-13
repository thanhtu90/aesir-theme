<?php
/**
 * Aesir Theme Functions
 * Version: 2.0 - Security & Performance Optimized
 *
 * CHANGELOG v2.0:
 * - Moved API keys to wp-config.php constants
 * - Added caching for external API calls
 * - Conditional asset loading
 * - Rate limiting for AJAX handlers
 * - Removed debug logging in production
 */

// ============================================================
// THEME SETUP
// ============================================================

// Add RSS links to <head> section
add_theme_support('automatic-feed-links');

// Enable featured image support
add_theme_support('post-thumbnails', array('post', 'page', 'product'));

// Register main menu
function register_my_main_menu() {
    register_nav_menu('main-menu', __('Main Menu'));
}
add_action('after_setup_theme', 'register_my_main_menu');

// ============================================================
// SCRIPTS & STYLES - OPTIMIZED LOADING
// ============================================================

/**
 * Replace default jQuery with Google CDN version
 * FIX: Added defer for better performance
 */
function aesir_replace_jquery() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script(
            'jquery',
            'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',
            [],
            '3.7.1',
            true // Load in footer
        );
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'aesir_replace_jquery');

/**
 * Enqueue main stylesheet
 */
function mytheme_enqueue_styles() {
    wp_enqueue_style(
        'tailwindcss',
        get_template_directory_uri() . '/assets/css/output.css',
        [],
        filemtime(get_template_directory() . '/assets/css/output.css')
    );
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_styles');

/**
 * Enqueue Swiper - CONDITIONALLY
 * FIX PERF-004: Only load on pages that need it
 */
function mytheme_enqueue_swiper() {
    // Only load Swiper on pages that use sliders
    if (is_product() || is_front_page() || is_shop() || is_product_category()) {
        // Use local copies instead of CDN for better caching
        wp_enqueue_style(
            'swiper-css',
            get_template_directory_uri() . '/assets/css/swiper-bundle.min.css',
            [],
            '11.1.14'
        );
        wp_enqueue_script(
            'swiper-js',
            get_template_directory_uri() . '/assets/js/swiper-bundle.min.js',
            [],
            '11.1.14',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_swiper');

/**
 * Enqueue theme scripts - extracted from inline
 * FIX PERF-005: Move inline JS to external files
 */
function mytheme_enqueue_scripts() {
    // Footer scripts (menu toggle, footer accordion)
    wp_enqueue_script(
        'aesir-footer',
        get_template_directory_uri() . '/assets/js/footer-scripts.js',
        ['jquery'],
        '2.0',
        true
    );

    // Product page specific scripts
    if (is_product() || is_product_category() || is_shop()) {
        wp_enqueue_script(
            'aesir-product',
            get_template_directory_uri() . '/assets/js/product-page.js',
            ['jquery'],
            filemtime(get_template_directory() . '/assets/js/product-page.js'),
            true
        );

        // Pass AJAX URL to script
        wp_localize_script('aesir-product', 'aesirAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aesir_stock_check')
        ]);
    }

    // Checkout page scripts
    if (function_exists('is_checkout') && is_checkout()) {
        wp_enqueue_script(
            'aesir-checkout',
            get_template_directory_uri() . '/assets/js/checkout-methods.js',
            ['jquery'],
            '2.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Get Pancake API credentials from wp-config.php
 * FIX SEC-001: Centralized secure credential retrieval
 */
function aesir_get_pancake_credentials() {
    return [
        'api_key' => defined('PANCAKE_API_KEY') ? PANCAKE_API_KEY : '',
        'shop_id' => defined('PANCAKE_SHOP_ID') ? PANCAKE_SHOP_ID : '',
        'warehouse_id' => defined('PANCAKE_WAREHOUSE_ID') ? PANCAKE_WAREHOUSE_ID : '',
        'webhook_secret' => defined('PANCAKE_WEBHOOK_SECRET') ? PANCAKE_WEBHOOK_SECRET : '',
    ];
}

/**
 * Rate limiting helper
 * FIX SEC-003: Prevent AJAX abuse
 *
 * @param string $action Action identifier
 * @param int $limit Max requests allowed
 * @param int $window Time window in seconds
 * @return bool True if within limit, false if rate limited
 */
function aesir_check_rate_limit($action, $limit = 30, $window = 60) {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $key = 'rate_limit_' . md5($ip . '_' . $action);
    $count = get_transient($key);

    if ($count === false) {
        set_transient($key, 1, $window);
        return true;
    }

    if ($count >= $limit) {
        return false;
    }

    set_transient($key, $count + 1, $window);
    return true;
}

/**
 * Secure logging helper - only logs in debug mode
 * FIX SEC-004: No logging in production
 */
function aesir_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $log_message = '[Aesir] ' . $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

// ============================================================
// INCLUDE FUNCTION MODULES
// ============================================================

// WooCommerce customizations
include_once get_template_directory() . '/functions/woo-custom.php';

// Thank you card feature
include_once get_template_directory() . '/functions/thankyou-card.php';

// Pancake stock (wp-config credentials, transients; must load before product-page inline script)
include_once get_template_directory() . '/functions/pancake-stock.php';

// Pancake integrations — product UI scripts only (handlers live in pancake-stock.php)
include_once get_template_directory() . '/functions/get-stock-pancake.php';
include_once get_template_directory() . '/functions/pancake-loyalty.php';    // Renamed & optimized
include_once get_template_directory() . '/functions/pancake-sync.php';
include_once get_template_directory() . '/functions/pancake-webhook.php';    // Renamed from order-update.php

// Checkout customizations
include_once get_template_directory() . '/functions/checkout-method.php';
include_once get_template_directory() . '/functions/checkout-state.php';

// Menu functions
include_once get_template_directory() . '/functions/child-menu.php';

// Language switcher (header)
include_once get_template_directory() . '/functions/language-switcher.php';

// Purchased products tracking
include_once get_template_directory() . '/functions/purchased-products.php';
