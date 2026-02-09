<?php
/**
 * WooCommerce → Pancake Order Sync
 * Version: 2.0 - Security Optimized
 *
 * FIXES APPLIED:
 * - SEC-001: API keys from wp-config.php
 * - SEC-004: No sensitive data logging
 */

defined('ABSPATH') || exit;

// Hook into WooCommerce order events
add_action('woocommerce_payment_complete', 'wc_to_pancake_send_order', 10, 1);
add_action('woocommerce_order_status_processing', 'wc_to_pancake_send_order', 10, 1);
add_action('woocommerce_order_status_on-hold', 'wc_to_pancake_send_order', 10, 1);
add_action('woocommerce_order_status_completed', 'wc_to_pancake_send_order', 10, 1);

/**
 * Send order to Pancake POS
 */
function wc_to_pancake_send_order($order_id) {
    // Get credentials securely from wp-config.php
    $credentials = aesir_get_pancake_credentials();

    if (empty($credentials['api_key']) || empty($credentials['shop_id'])) {
        aesir_log('Pancake sync: API credentials not configured');
        return;
    }

    $api_key = $credentials['api_key'];
    $shop_id = $credentials['shop_id'];
    $warehouse_id = $credentials['warehouse_id'];

    $order = wc_get_order($order_id);
    if (!$order) return;

    // Prevent duplicate sends
    if (get_post_meta($order_id, '_pancake_order_sent', true)) {
        return;
    }

    // Payment method logic
    $method = $order->get_payment_method();
    $status = $order->get_status();

    // BACS → send on on-hold
    if ($method === 'bacs' && !in_array($status, ['on-hold', 'processing', 'completed'], true)) {
        return;
    }

    // COD → send on processing
    if ($method === 'cod' && !in_array($status, ['processing', 'completed'], true)) {
        return;
    }

    // PayPal & others → paid only
    if (!in_array($method, ['cod', 'bacs'], true)) {
        if (!$order->is_paid() || !in_array($status, ['processing', 'completed'], true)) {
            return;
        }
    }

    // Build order data
    $data = [
        'order_id' => $order_id,
        'order_key' => $order->get_order_key(),
        'status' => $order->get_status(),
        'currency' => $order->get_currency(),
        'created_at' => $order->get_date_created(),
        'payment_method' => $order->get_payment_method(),
        'payment_method_title' => $order->get_payment_method_title(),
        'transaction_id' => $order->get_transaction_id(),
        'shipping_total' => $order->get_shipping_total(),
        'shipping_method' => $order->get_shipping_method(),
        'subtotal' => $order->get_subtotal(),
        'discount_total' => $order->get_discount_total(),
        'total' => $order->get_total(),
        'billing' => $order->get_address('billing'),
        'shipping' => $order->get_address('shipping'),
        'customer_note' => $order->get_customer_note(),
    ];

    // Get meta data
    $meta = [];
    foreach ($order->get_meta_data() as $meta_item) {
        $meta[$meta_item->key] = $meta_item->value;
    }
    $data['meta'] = $meta;

    // Get shipping lines
    $shipping_lines = [];
    foreach ($order->get_items('shipping') as $ship_item) {
        $shipping_lines[] = [
            'name' => $ship_item->get_name(),
            'method_id' => $ship_item->get_method_id(),
            'total' => $ship_item->get_total(),
        ];
    }
    $data['shipping_lines'] = $shipping_lines;

    // Build Pancake payload
    $payload = [
        'bill_email' => $data['billing']['email'] ?? null,
        'bill_full_name' => $order->get_formatted_billing_full_name(),
        'bill_phone_number' => $order->get_billing_phone(),
        'is_free_shipping' => false,
        'received_at_shop' => false,
        'account' => '307028465',
        'account_name' => 'Aesir: Website',
        'assigning_seller_id' => null,
        'items' => [],
        'note' => '',
        'note_print' => null,
        'returned_reason' => 1,
        'warehouse_id' => $warehouse_id,
        'shipping_address' => [
            'address' => trim(implode(' ', array_filter([
                $data['shipping']['address_1'] ?? '',
                $data['shipping']['city'] ?? '',
                $data['shipping']['state'] ?? '',
                $data['shipping']['country'] ?? '',
            ]))),
            'commune_id' => null,
            'country_code' => null,
            'district_id' => null,
            'full_address' => trim(implode(' ', array_filter([
                $data['shipping']['address_1'] ?? '',
                $data['shipping']['city'] ?? '',
                $data['shipping']['state'] ?? '',
                $data['shipping']['country'] ?? '',
            ]))),
            'full_name' => trim(($data['shipping']['first_name'] ?? '') . ' ' . ($data['shipping']['last_name'] ?? '')),
            'phone_number' => $data['shipping']['phone'] ?? null,
            'post_code' => $data['shipping']['postcode'] ?? null,
            'province_id' => null,
        ],
        'shipping_fee' => intval($order->get_shipping_total()),
        'shop_id' => $shop_id,
        'total_discount' => intval($order->get_discount_total()),
        'warehouse_info' => [
            'district_id' => null,
            'full_address' => '',
            'name' => '',
            'phone_number' => '',
            'province_id' => null,
        ],
        'custom_id' => "WC-$order_id",
        'activated_promotion_advances' => [],
        'status' => 0,
        'cod' => $data['payment_method'] === 'cod' ? intval($data['total']) : 0,
    ];

    // Build note
    $gift_card = $meta['_wc_other/aesir/thankyou_card'] ?? ($meta['thankyou_card'] ?? 'None');
    $thankyou_message = $meta['_wc_other/aesir/thankyou_message'] ?? ($meta['thankyou_message'] ?? 'None');
    $shipping_method_name = $shipping_lines[0]['name'] ?? 'None';
    $customer_note = $data['customer_note'] ?: 'None';

    $payload['note'] = sprintf(
        'Payment Method: %s. Shipping Method: %s. Gift Card: %s. Thank you Message: %s. Customer Note: %s.',
        $data['payment_method_title'],
        $shipping_method_name,
        $gift_card,
        $thankyou_message,
        $customer_note
    );

    // Build items
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) continue;

        $payload['items'][] = [
            'discount_each_product' => 0,
            'is_bonus_product' => false,
            'is_discount_percent' => false,
            'is_wholesale' => false,
            'one_time_product' => false,
            'quantity' => (int)$item->get_quantity(),
            'variation_id' => $product->get_sku() ?: $product->get_id(),
            'product_id' => $product->get_id(),
            'variation_info' => [
                'detail' => null,
                'fields' => null,
                'display_id' => null,
                'name' => $item->get_name(),
                'product_display_id' => null,
                'retail_price' => intval($item->get_total() / max($item->get_quantity(), 1)),
                'weight' => intval($product->get_weight() ?? 0),
            ],
        ];
    }

    // Send to Pancake
    $url = "https://pos.pages.fm/api/v1/shops/{$shop_id}/orders?api_key={$api_key}";

    $response = wp_remote_post($url, [
        'timeout' => 30,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($payload),
    ]);

    if (is_wp_error($response)) {
        aesir_log('Pancake sync failed for order ' . $order_id, $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $response_json = json_decode($body, true);

    if (!empty($response_json['success'])) {
        update_post_meta($order_id, '_pancake_order_sent', 1);

        if (isset($response_json['data']['order_link'])) {
            update_post_meta($order_id, '_pancake_order_link', $response_json['data']['order_link']);
        }

        aesir_log('Pancake sync successful for order ' . $order_id);
    } else {
        aesir_log('Pancake sync response error for order ' . $order_id);
    }
}
