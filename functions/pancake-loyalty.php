<?php
/**
 * Pancake POS Loyalty Points Integration
 * Version: 2.0 - Performance Optimized
 *
 * FIXES APPLIED:
 * - SEC-001: API keys from wp-config.php
 * - PERF-001: Added caching for loyalty data
 */

defined('ABSPATH') || exit;

/**
 * Get customer loyalty data from Pancake
 *
 * @param string $email Customer email
 * @param bool $force_refresh Force cache refresh
 * @return array|false Loyalty data or false on failure
 */
function pancake_get_customer_loyalty($email, $force_refresh = false) {
    if (empty($email) || !is_email($email)) {
        return false;
    }

    // Get credentials securely
    $credentials = aesir_get_pancake_credentials();

    if (empty($credentials['api_key']) || empty($credentials['shop_id'])) {
        aesir_log('Loyalty: Pancake API credentials not configured');
        return false;
    }

    $api_key = $credentials['api_key'];
    $shop_id = $credentials['shop_id'];
    $base_url = 'https://pos.pages.fm/api/v1';

    // Cache key based on email
    $cache_key = 'pancake_loyalty_' . md5($email);
    $cache_ttl = defined('AESIR_LOYALTY_CACHE_TTL') ? AESIR_LOYALTY_CACHE_TTL : 600;

    // Check cache first
    if (!$force_refresh) {
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
    }

    // Search for customer by email
    $search_url = "{$base_url}/shops/{$shop_id}/customers/search?api_key={$api_key}&keyword=" . urlencode($email);

    $response = wp_remote_get($search_url, [
        'timeout' => 10,
        'sslverify' => true,
    ]);

    if (is_wp_error($response)) {
        aesir_log('Loyalty API error', $response->get_error_message());
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['data'][0]['id'])) {
        // Cache "not found" for shorter time
        set_transient($cache_key, ['found' => false], 300);
        return false;
    }

    $customer = $data['data'][0];
    $customer_id = $customer['id'];
    $points = isset($customer['loyalty_point']) ? intval($customer['loyalty_point']) : 0;

    // Get loyalty history
    $history_url = "{$base_url}/shops/{$shop_id}/customers/{$customer_id}/loyalty_histories?api_key={$api_key}";
    $history_response = wp_remote_get($history_url, ['timeout' => 10]);

    $history = [];
    if (!is_wp_error($history_response)) {
        $history_data = json_decode(wp_remote_retrieve_body($history_response), true);
        $history = $history_data['data'] ?? [];
    }

    $result = [
        'found' => true,
        'points' => $points,
        'history' => $history,
        'customer_id' => $customer_id,
        'cached_at' => time(),
    ];

    // Cache successful result
    set_transient($cache_key, $result, $cache_ttl);

    return $result;
}

/**
 * Display loyalty points on WooCommerce account dashboard
 */
function pancake_show_loyalty_on_account_page() {
    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();
    $email = $user->user_email;

    $loyalty = pancake_get_customer_loyalty($email);

    echo '<section class="woocommerce-loyalty-section" style="margin-top:30px;">';
    echo '<h2>' . esc_html__('Your Loyalty Points', 'aesir') . '</h2>';

    if (!$loyalty || empty($loyalty['found'])) {
        echo '<p>' . esc_html__('We couldn\'t find your loyalty account. Points will appear after your first purchase.', 'aesir') . '</p>';
    } else {
        echo '<p><strong>' . esc_html__('Current Points:', 'aesir') . '</strong> ' . esc_html($loyalty['points']) . '</p>';

        if (!empty($loyalty['history'])) {
            echo '<h3>' . esc_html__('Recent Activity', 'aesir') . '</h3>';
            echo '<ul class="loyalty-history">';

            foreach (array_slice($loyalty['history'], 0, 5) as $entry) {
                $desc = $entry['description'] ?? '';
                $points = $entry['points'] ?? 0;
                $type = $points >= 0 ? __('Earned', 'aesir') : __('Used', 'aesir');
                $date = isset($entry['created_at']) ? date_i18n('M j, Y', strtotime($entry['created_at'])) : '';

                printf(
                    '<li>%s</li>',
                    esc_html(sprintf('%s %d points — %s (%s)', $type, abs($points), $desc, $date))
                );
            }

            echo '</ul>';
        } else {
            echo '<p>' . esc_html__('No recent loyalty activity.', 'aesir') . '</p>';
        }
    }

    echo '</section>';
}
add_action('woocommerce_account_dashboard', 'pancake_show_loyalty_on_account_page');

/**
 * Clear loyalty cache for a user
 */
function aesir_clear_loyalty_cache($email) {
    delete_transient('pancake_loyalty_' . md5($email));
}

/**
 * Clear loyalty cache when order is placed
 */
add_action('woocommerce_order_status_completed', function($order_id) {
    $order = wc_get_order($order_id);
    if ($order) {
        $email = $order->get_billing_email();
        if ($email) {
            aesir_clear_loyalty_cache($email);
        }
    }
});
