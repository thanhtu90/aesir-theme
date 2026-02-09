<?php
/**
 * Checkout Payment Method Handler
 * Version: 2.0
 *
 * FIXES APPLIED:
 * - PERF-005: Moved inline JS to external file
 */

defined('ABSPATH') || exit;

/**
 * Enqueue checkout JS only on checkout page
 * JS moved to assets/js/checkout-methods.js
 */
add_action('wp_enqueue_scripts', function() {
    if (function_exists('is_checkout') && is_checkout()) {
        wp_enqueue_script(
            'aesir-checkout-methods',
            get_template_directory_uri() . '/assets/js/checkout-methods.js',
            ['jquery'],
            '2.0',
            true
        );
    }
});

/**
 * PayPal pre-checkout hook
 */
add_action('woocommerce_before_checkout_process', function() {
    if (WC()->payment_gateways) {
        $chosen_gateway = WC()->session->get('chosen_payment_method');

        if (strpos($chosen_gateway, 'paypal') !== false) {
            remove_filter('woocommerce_currency', ['WCPBC_Frontend_Pricing', 'get_currency'], 999);

            if (class_exists('WCPBC_Frontend_Pricing')) {
                WCPBC_Frontend_Pricing::remove_hooks();
            }
        }
    }
}, 5);

/**
 * Fix PayPal request body for currencies without decimals
 */
add_filter('woocommerce_paypal_payments_request_body', function($body) {
    if (empty($body['purchase_units'])) {
        return $body;
    }

    $no_decimal_currencies = [
        'KRW', 'VND', 'JPY', 'TWD', 'HUF', 'UGX',
        'CLP', 'ISK', 'PYG', 'RWF', 'VUV'
    ];

    $base_currency = get_option('woocommerce_currency');

    foreach ($body['purchase_units'] as &$unit) {
        if (empty($unit['amount'])) {
            continue;
        }

        $unit['amount']['currency_code'] = $base_currency;

        if (in_array($base_currency, $no_decimal_currencies, true)) {
            if (isset($unit['amount']['value'])) {
                $unit['amount']['value'] = (string)intval(round($unit['amount']['value']));
            }

            if (!empty($unit['amount']['breakdown'])) {
                foreach ($unit['amount']['breakdown'] as &$item) {
                    if (isset($item['value'])) {
                        $item['value'] = (string)intval(round($item['value']));
                    }
                }
            }
        }
    }

    return $body;
}, 999);
