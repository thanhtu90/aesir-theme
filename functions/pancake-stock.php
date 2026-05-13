<?php
/**
 * Pancake POS Stock Integration
 * Version: 2.0 - Performance Optimized
 *
 * FIXES APPLIED:
 * - PERF-001: Added WordPress Transients caching
 * - PERF-002: Optimized N+1 queries with batch caching
 * - SEC-001: API keys from wp-config.php
 * - SEC-003: Rate limiting on AJAX endpoints
 * - SEC-004: Removed debug logging in production
 */

defined('ABSPATH') || exit;

// ============================================================
// CORE STOCK FUNCTION WITH CACHING
// ============================================================

/**
 * Transient key fragment so stock cache invalidates when PANCAKE_WAREHOUSE_ID changes.
 */
function aesir_pancake_stock_wh_cache_tag() {
    $credentials = aesir_get_pancake_credentials();
    $wh = $credentials['warehouse_id'] ?? '';

    return $wh !== '' ? md5($wh) : '_nowh';
}

/**
 * Single-request fetch: product by SKU in Pancake, pick variation, resolve warehouse stock.
 *
 * @return array|false
 */
function aesir_pancake_fetch_product_stock($sku, $display_id, $api_key, $shop_id, $base_url, $warehouse_id) {
    $product_url = "{$base_url}/shops/{$shop_id}/products/" . urlencode($sku) . "?api_key={$api_key}";

    $response = wp_remote_get($product_url, [
        'timeout' => 60,
        'sslverify' => true,
    ]);

    if (is_wp_error($response)) {
        aesir_log('Pancake API Error', $response->get_error_message());

        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['success']) || empty($data['data'])) {
        return false;
    }

    $product = $data['data'];
    $variations = $product['variations'] ?? [];
    $variation = null;

    if ($display_id !== null && $display_id !== '') {
        foreach ($variations as $var) {
            $var_display_id = trim((string)($var['display_id'] ?? ''));
            if ($var_display_id === trim((string)$display_id)) {
                $variation = $var;
                break;
            }
        }

        if ($variation === null) {
            return false;
        }
    } else {
        if (count($variations) === 1) {
            $variation = $variations[0];
        } elseif (!empty($variations)) {
            foreach ($variations as $var) {
                $var_display_id = trim((string)($var['display_id'] ?? ''));
                if ($var_display_id === $sku) {
                    $variation = $var;
                    break;
                }
            }
            if ($variation === null) {
                $variation = $variations[0];
            }
        }
    }

    if ($variation === null) {
        return false;
    }

    $stock = 0;
    $found_warehouse = false;

    foreach ($variation['variations_warehouses'] ?? [] as $wh) {
        if (($wh['warehouse_id'] ?? '') === $warehouse_id) {
            $stock = (int)($wh['remain_quantity'] ?? 0);
            $found_warehouse = true;
            break;
        }
    }

    if (!$found_warehouse) {
        $stock = (int)($variation['remain_quantity'] ?? 0);
    }

    return [
        'stock' => $stock,
        'product_name' => $product['name'] ?? '',
        'id' => $product['id'] ?? '',
        'variation_display_id' => $variation['display_id'] ?? '',
        'cached_at' => time(),
    ];
}

/**
 * Get stock quantity from Pancake POS by SKU
 * FIX PERF-001: Results are cached using WordPress Transients
 * FIX: Cache keys include warehouse id so changing PANCAKE_WAREHOUSE_ID does not serve stale quantities.
 * STRATEGY: Try full SKU as product id first; if not found and SKU contains "-", try base SKU + full SKU as display_id.
 *
 * @param string $sku Product SKU
 * @param string|null $display_id Variation display ID
 * @param bool $force_refresh Force cache refresh
 * @return array|false Stock data or false on failure
 */
function get_pancake_stock($sku, $display_id = null, $force_refresh = false) {
    $credentials = aesir_get_pancake_credentials();

    if (empty($credentials['api_key']) || empty($credentials['shop_id'])) {
        aesir_log('Pancake API credentials not configured');
        return false;
    }

    $api_key = $credentials['api_key'];
    $shop_id = $credentials['shop_id'];
    $warehouse_id = $credentials['warehouse_id'];
    $base_url = 'https://pos.pages.fm/api/v1';
    $wh_tag = aesir_pancake_stock_wh_cache_tag();
    $cache_ttl = defined('AESIR_STOCK_CACHE_TTL') ? AESIR_STOCK_CACHE_TTL : 300;

    $cache_key = function ($s, $d) use ($wh_tag) {
        return 'pancake_stock_' . md5($s . '_' . ($d ?? 'null') . '_' . $wh_tag);
    };

    if ($display_id === null || $display_id === '') {
        if (!$force_refresh) {
            $cached = get_transient($cache_key($sku, null));
            if ($cached !== false) {
                return $cached;
            }
        }

        $result = aesir_pancake_fetch_product_stock($sku, null, $api_key, $shop_id, $base_url, $warehouse_id);

        if ($result !== false) {
            set_transient($cache_key($sku, null), $result, $cache_ttl);

            return $result;
        }

        if (strpos($sku, '-') !== false) {
            $parts = explode('-', $sku, 2);
            $base_sku = $parts[0];
            $variation_display_id = $sku;

            if (!$force_refresh) {
                $cached2 = get_transient($cache_key($base_sku, $variation_display_id));
                if ($cached2 !== false) {
                    return $cached2;
                }
            }

            $result = aesir_pancake_fetch_product_stock(
                $base_sku,
                $variation_display_id,
                $api_key,
                $shop_id,
                $base_url,
                $warehouse_id
            );

            if ($result !== false) {
                set_transient($cache_key($base_sku, $variation_display_id), $result, $cache_ttl);

                return $result;
            }
        }

        set_transient($cache_key($sku, null), false, 60);

        return false;
    }

    $ck = $cache_key($sku, $display_id);

    if (!$force_refresh) {
        $cached = get_transient($ck);
        if ($cached !== false) {
            return $cached;
        }
    }

    $result = aesir_pancake_fetch_product_stock(
        $sku,
        $display_id,
        $api_key,
        $shop_id,
        $base_url,
        $warehouse_id
    );

    if ($result !== false) {
        set_transient($ck, $result, $cache_ttl);
    } else {
        set_transient($ck, false, 60);
    }

    return $result;
}

/**
 * Clear stock cache for a specific SKU
 */
function aesir_clear_stock_cache($sku, $display_id = null) {
    $wh_tag = aesir_pancake_stock_wh_cache_tag();
    $cache_key = 'pancake_stock_' . md5($sku . '_' . ($display_id ?? 'null') . '_' . $wh_tag);
    delete_transient($cache_key);
}

/**
 * Clear all product variations stock cache
 */
function aesir_clear_product_stock_cache($product_id) {
    $wh_tag = aesir_pancake_stock_wh_cache_tag();
    delete_transient('product_variations_stock_' . $product_id . '_' . $wh_tag);

    $product = wc_get_product($product_id);
    if ($product && $product->is_type('variable')) {
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                $sku = $variation->get_sku();
                if ($sku) {
                    aesir_clear_stock_cache($sku);
                }
            }
        }
    }
}

// ============================================================
// AJAX HANDLERS
// ============================================================

add_action('wp_ajax_get_pancake_stock', 'ajax_get_pancake_stock');
add_action('wp_ajax_nopriv_get_pancake_stock', 'ajax_get_pancake_stock');

/**
 * AJAX handler for single SKU stock check
 * FIX SEC-003: Added rate limiting
 */
function ajax_get_pancake_stock() {
    // Rate limiting
    $rate_limit = defined('AESIR_RATE_LIMIT_STOCK') ? AESIR_RATE_LIMIT_STOCK : 30;
    if (!aesir_check_rate_limit('pancake_stock', $rate_limit, 60)) {
        wp_send_json_error('Rate limit exceeded. Please wait.', 429);
        return;
    }

    $sku = sanitize_text_field(wp_unslash($_POST['sku'] ?? ''));
    $display_raw = isset($_POST['display_id']) ? wp_unslash($_POST['display_id']) : '';
    $display_trim = is_string($display_raw) ? trim($display_raw) : '';
    // jQuery may omit the field or send the literal string "null" — never treat that as a real display_id.
    $display_id = ($display_trim === '' || strtolower($display_trim) === 'null') ? null : sanitize_text_field($display_raw);

    if (empty($sku)) {
        wp_send_json_error('No SKU provided');
        return;
    }

    $result = get_pancake_stock($sku, $display_id);

    if ($result === false) {
        wp_send_json_error('No stock data found');
    } else {
        wp_send_json_success($result);
    }
}

add_action('wp_ajax_get_product_variations_stock', 'ajax_get_product_variations_stock');
add_action('wp_ajax_nopriv_get_product_variations_stock', 'ajax_get_product_variations_stock');

/**
 * AJAX handler for all variations stock
 * FIX PERF-002: Cache entire product variations result
 */
function ajax_get_product_variations_stock() {
    // Rate limiting
    $rate_limit = defined('AESIR_RATE_LIMIT_STOCK') ? AESIR_RATE_LIMIT_STOCK : 30;
    if (!aesir_check_rate_limit('variations_stock', $rate_limit, 60)) {
        wp_send_json_error(['message' => 'Rate limit exceeded']);
        return;
    }

    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id) {
        wp_send_json_error(['message' => 'Missing product ID']);
        return;
    }

    // Check cache first (warehouse-scoped so a warehouse switch does not reuse old aggregates)
    $wh_tag = aesir_pancake_stock_wh_cache_tag();
    $cache_key = 'product_variations_stock_' . $product_id . '_' . $wh_tag;
    $cache_ttl = defined('AESIR_STOCK_CACHE_TTL') ? AESIR_STOCK_CACHE_TTL : 300;

    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
        return;
    }

    $product = wc_get_product($product_id);
    if (!$product || $product->get_type() !== 'variable') {
        wp_send_json_error(['message' => 'Not a variable product']);
        return;
    }

    // Get all variations in one optimized query
    $variations = wc_get_products([
        'type' => 'variation',
        'parent' => $product_id,
        'limit' => -1,
        'return' => 'objects',
    ]);

    $result = [];

    foreach ($variations as $variation) {
        $sku = $variation->get_sku();
        if (!$sku) continue;

        $stock_data = get_pancake_stock($sku);
        $attributes = $variation->get_attributes();

        $result[] = [
            'sku' => $sku,
            'attributes' => $attributes,
            'stock' => $stock_data['stock'] ?? 0,
            'variation_id' => $variation->get_id(),
        ];
    }

    // Cache the complete result
    set_transient($cache_key, $result, $cache_ttl);

    wp_send_json_success($result);
}

add_action('wp_ajax_check_acf_gallery', 'ajax_check_acf_gallery');
add_action('wp_ajax_nopriv_check_acf_gallery', 'ajax_check_acf_gallery');

/**
 * AJAX handler for ACF gallery image
 */
function ajax_check_acf_gallery() {
    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id) {
        wp_send_json_error(['message' => 'Missing product ID']);
        return;
    }

    // Check cache
    $cache_key = 'acf_gallery_image_' . $product_id;
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        if ($cached === 'none') {
            wp_send_json_error(['message' => 'No ACF gallery found']);
        } else {
            wp_send_json_success(['image_url' => $cached]);
        }
        return;
    }

    // Get gallery from ACF
    if (!function_exists('get_field')) {
        wp_send_json_error(['message' => 'ACF not available']);
        return;
    }

    $gallery = get_field('gallery', $product_id);

    if ($gallery && is_array($gallery) && count($gallery) > 0) {
        foreach ($gallery as $item) {
            if (empty($item['is_video']) && !empty($item['image'])) {
                $image_id = $item['image'];
                $image_url = wp_get_attachment_url($image_id);
                if ($image_url) {
                    // Cache for 1 hour
                    set_transient($cache_key, $image_url, HOUR_IN_SECONDS);
                    wp_send_json_success(['image_url' => $image_url]);
                    return;
                }
            }
        }
    }

    // Cache "not found"
    set_transient($cache_key, 'none', HOUR_IN_SECONDS);
    wp_send_json_error(['message' => 'No ACF gallery found']);
}

// ============================================================
// CHECKOUT STOCK VALIDATION
// ============================================================

add_action('woocommerce_checkout_process', 'aesir_validate_pancake_stock_before_checkout');

/**
 * Validate stock before checkout
 */
function aesir_validate_pancake_stock_before_checkout() {
    if (is_admin() && !defined('DOING_AJAX')) return;

    $errors = [];

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $qty = intval($cart_item['quantity']);
        if (!$product) continue;

        $sku = $product->get_sku();
        if (empty($sku) && $product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            $sku = $parent ? $parent->get_sku() : '';
        }
        if (empty($sku)) continue;

        // Force refresh stock for checkout validation
        $stock_data = get_pancake_stock($sku, null, true);

        // Fallback with display_id
        if (($stock_data === false || !isset($stock_data['stock'])) && strpos($sku, '-') !== false) {
            $parts = explode('-', $sku, 2);
            $base_sku = $parts[0];
            $stock_data = get_pancake_stock($base_sku, $sku, true);
        }

        if ($stock_data === false || !isset($stock_data['stock'])) {
            $errors[] = sprintf(
                '%s (SKU: %s) could not be verified. Please try again or contact support.',
                $product->get_name(),
                $sku
            );
            continue;
        }

        $available = intval($stock_data['stock']);
        if ($available < $qty) {
            if ($available <= 0) {
                $errors[] = sprintf(
                    '%s (SKU: %s) is out of stock. Please remove it from your cart.',
                    $product->get_name(),
                    $sku
                );
            } else {
                $errors[] = sprintf(
                    'Only %d left in stock for %s (SKU: %s). You have %d in your cart.',
                    $available,
                    $product->get_name(),
                    $sku,
                    $qty
                );
            }
        }
    }

    foreach ($errors as $err) {
        wc_add_notice($err, 'error');
    }
}

// ============================================================
// PRODUCT PAGE — stock line mount (below Add to cart, above wishlist)
// ============================================================

add_action('woocommerce_after_add_to_cart_button', function () {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    global $product;
    if (!$product || !$product->is_type('variable')) {
        return;
    }
    echo '<div id="pancake-stock-info" class="aesir-pancake-stock-line" aria-live="polite"></div>';
}, 5);

// ============================================================
// CACHE INVALIDATION HOOKS
// ============================================================

/**
 * Clear stock cache when order status changes
 */
add_action('woocommerce_order_status_changed', function($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product) {
            $sku = $product->get_sku();
            if ($sku) {
                aesir_clear_stock_cache($sku);
            }

            // Also clear parent product cache for variations
            if ($product->is_type('variation')) {
                aesir_clear_product_stock_cache($product->get_parent_id());
            }
        }
    }
});
