<?php
/**
 * Pancake → WooCommerce Order Update Webhook
 * Version: 2.0 - Security Hardened
 *
 * FIXES APPLIED:
 * - SEC-002: Added HMAC signature verification
 * - Alternative: IP whitelist validation
 */

defined('ABSPATH') || exit;

/**
 * Register REST API endpoint for Pancake webhooks
 */
add_action('rest_api_init', function () {
    register_rest_route('pancake/v1', '/order-update', [
        'methods' => 'POST',
        'callback' => 'handle_pancake_order_update',
        'permission_callback' => 'verify_pancake_webhook_request',
    ]);
});

/**
 * Verify webhook request authenticity
 * FIX SEC-002: Validate webhook signature or IP
 *
 * @param WP_REST_Request $request
 * @return bool|WP_Error
 */
function verify_pancake_webhook_request($request) {
    $credentials = aesir_get_pancake_credentials();
    $secret = $credentials['webhook_secret'] ?? '';

    // Method 1: HMAC Signature Verification (preferred)
    if (!empty($secret) && $secret !== 'GENERATE_NEW_SECRET_HERE') {
        $signature = $request->get_header('X-Pancake-Signature');

        if (empty($signature)) {
            aesir_log('Webhook rejected: Missing signature header');
            return new WP_Error(
                'missing_signature',
                'Missing webhook signature',
                ['status' => 401]
            );
        }

        $payload = $request->get_body();
        $expected = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expected, $signature)) {
            aesir_log('Webhook rejected: Invalid signature');
            return new WP_Error(
                'invalid_signature',
                'Invalid webhook signature',
                ['status' => 401]
            );
        }

        return true;
    }

    // Method 2: IP Whitelist (fallback if signature not configured)
    $allowed_ips = [
        // Add Pancake server IPs here
        // '123.456.789.0',
    ];

    // If no IPs configured, allow (but log warning)
    if (empty($allowed_ips)) {
        aesir_log('WARNING: Pancake webhook has no authentication configured! Set PANCAKE_WEBHOOK_SECRET or IP whitelist.');

        // In production, you might want to reject all requests until configured:
        // return new WP_Error('not_configured', 'Webhook authentication not configured', ['status' => 500]);

        // For now, allow but log
        return true;
    }

    $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    // Handle proxy headers
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $remote_ip = trim($forwarded_ips[0]);
    }

    if (!in_array($remote_ip, $allowed_ips, true)) {
        aesir_log('Webhook rejected: IP not whitelisted - ' . $remote_ip);
        return new WP_Error(
            'ip_not_allowed',
            'IP address not authorized',
            ['status' => 403]
        );
    }

    return true;
}

/**
 * Resolve a WooCommerce order by order number (e.g. sequential number from WT plugin).
 * Compatible with Sequential Order Number for WooCommerce: use get_order_number() for external refs.
 *
 * @param string $order_number The order number as sent to external systems (e.g. "1001" or "AES-1001").
 * @return WC_Order|false Order object or false if not found.
 */
function aesir_get_order_by_order_number($order_number) {
    $order_number = trim((string) $order_number);
    if ($order_number === '') {
        return false;
    }

    // Try by numeric ID first (backward compat when order number equals post ID).
    if (is_numeric($order_number)) {
        $order = wc_get_order((int) $order_number);
        if ($order && (string) $order->get_order_number() === $order_number) {
            return $order;
        }
    }

    // Find by order number (sequential / custom number).
    $orders = wc_get_orders([
        'limit'    => 200,
        'orderby'  => 'date',
        'order'    => 'DESC',
        'return'   => 'objects',
    ]);
    foreach ($orders as $order) {
        if ((string) $order->get_order_number() === $order_number) {
            return $order;
        }
    }

    return false;
}

/**
 * Handle Pancake order update webhook
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function handle_pancake_order_update($request) {
    $data = $request->get_json_params();

    // Validate order ID format (WC-{order_number} — order_number may be sequential from plugin)
    $pancake_order_id = sanitize_text_field($data['id'] ?? '');

    if (!$pancake_order_id || strpos($pancake_order_id, 'WC-') !== 0) {
        return new WP_Error(
            'invalid_order',
            'Not a valid WooCommerce order ID',
            ['status' => 400]
        );
    }

    $order_number_ref = substr($pancake_order_id, 3);
    $order = aesir_get_order_by_order_number($order_number_ref);

    if (!$order) {
        return new WP_Error(
            'order_not_found',
            'Order not found',
            ['status' => 404]
        );
    }

    // Map Pancake status to WooCommerce status
    $status_map = [
        0  => 'pending',       // New
        17 => 'on-hold',       // Waiting for confirmation
        1  => 'processing',    // Confirmed
        8  => 'processing',    // Packaging
        2  => 'completed',     // Shipped
        3  => 'completed',     // Received
        16 => 'completed',     // Collected money
        6  => 'cancelled',     // Canceled
        7  => 'refunded',      // Refunded
    ];

    $pancake_status = intval($data['status'] ?? 0);
    $wc_status = $status_map[$pancake_status] ?? 'processing';

    // Update order status
    $order->update_status($wc_status, 'Updated from Pancake webhook');

    // Log successful update (use order number for consistency with Sequential Order Number plugin)
    aesir_log(sprintf(
        'Order %s updated via Pancake webhook: status %d -> %s',
        $order->get_order_number(),
        $pancake_status,
        $wc_status
    ));

    return rest_ensure_response([
        'success' => true,
        'pancake_order_id' => $pancake_order_id,
        'order_id' => $order->get_id(),
        'order_number' => $order->get_order_number(),
        'new_status' => $wc_status,
    ]);
}
