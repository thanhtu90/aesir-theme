<?php
// 1) Auto-detect block checkout. Register block fields when needed;
//    otherwise attach legacy output. (Paste into functions.php or plugin.)

add_action('init', 'aesir_register_or_attach_thankyou_fields');
function aesir_register_or_attach_thankyou_fields()
{
    // If WooCommerce isn't loaded yet, defer to its init hook so we don't call
    // wc_get_page_id() too early (this caused a fatal error when WC wasn't
    // available during admin bootstrap).
    if (!function_exists('wc_get_page_id')) {
        // Attach once to run after WooCommerce initializes.
        add_action('woocommerce_init', 'aesir_register_or_attach_thankyou_fields');
        return;
    }

    // Safe to call WooCommerce functions now.
    $checkout_id = wc_get_page_id('checkout');
    $is_block = false;

    if ($checkout_id && function_exists('has_block')) {
        $post = get_post($checkout_id);
        if ($post && has_block('woocommerce/checkout', $post)) {
            $is_block = true;
        }
    }

    // If block checkout: use the Blocks API to register additional fields
    if ($is_block && function_exists('woocommerce_register_additional_checkout_field')) {

        // Card select (order location)
        woocommerce_register_additional_checkout_field(
            array(
                'id' => 'aesir/thankyou_card',
                'label' => __('Select a Thank You Card', 'aesir'),
                'location' => 'order',
                'type' => 'select',
                'placeholder' => __('Select a card', 'aesir'),
                'options' => array(
                    array('value' => '', 'label' => __('No card', 'aesir')),
                    array('value' => 'classic', 'label' => __('Classic Card', 'aesir')),
                    array('value' => 'modern', 'label' => __('Modern Card', 'aesir')),
                    array('value' => 'floral', 'label' => __('Floral Card', 'aesir')),
                    array('value' => 'minimal', 'label' => __('Minimal Card', 'aesir')),
                ),
                'required' => false,
            )
        );

        // Message field: Blocks support 'text' but not textarea — use text with maxlength.
        woocommerce_register_additional_checkout_field(
            array(
                'id' => 'aesir/thankyou_message',
                'label' => __('Gift Message', 'aesir'),
                'location' => 'order',
                'type' => 'text', // 'textarea' not supported by the Blocks API
                'required' => false,
                'attributes' => array(
                    'maxLength' => 500,
                ),
            )
        );

        return;
    }

    // Legacy checkout fallback: display after order notes (works for non-block checkout)
    add_action('woocommerce_after_order_notes', 'aesir_add_thankyou_card_fields_legacy');
}

function aesir_add_thankyou_card_fields_legacy($checkout)
{
    echo '<div id="thankyou_card_fields"><h3>' . esc_html__('Thank You Card', 'aesir') . '</h3>';

    woocommerce_form_field(
        'thankyou_card',
        array(
            'type' => 'select',
            'class' => array('form-row-wide'),
            'label' => __('Select a Thank You Card', 'aesir'),
            'options' => array(
                '' => __('No card', 'aesir'),
                'classic' => __('Classic Card', 'aesir'),
                'modern' => __('Modern Card', 'aesir'),
                'floral' => __('Floral Card', 'aesir'),
                'minimal' => __('Minimal Card', 'aesir'),
            ),
        ),
        $checkout->get_value('thankyou_card')
    );

    woocommerce_form_field(
        'thankyou_message',
        array(
            'type' => 'textarea',
            'class' => array('form-row-wide'),
            'label' => __('Gift Message', 'aesir'),
            'placeholder' => __('Write your personalized thank you message here...', 'aesir'),
            'required' => false,
        ),
        $checkout->get_value('thankyou_message')
    );

    echo '</div>';
}

// 2) Optional validation (legacy style)
add_action('woocommerce_checkout_process', 'aesir_validate_thankyou_card_fields');
function aesir_validate_thankyou_card_fields()
{
    // Example: require message when a card is selected (legacy POST key)
    if (!empty($_POST['thankyou_card']) && empty($_POST['thankyou_message'])) {
        wc_add_notice(__('Please enter a message for your selected Thank You card.', 'aesir'), 'error');
    }
}

// 3) Save to order meta (handles both legacy and block-submitted keys; also copies _wc_other/* block meta if present)
add_action('woocommerce_checkout_create_order', 'aesir_save_thankyou_card_fields', 10, 2);
function aesir_save_thankyou_card_fields($order, $data)
{

    // thankyou_card
    if (isset($_POST['thankyou_card'])) {
        $order->update_meta_data('thankyou_card', sanitize_text_field(wp_unslash($_POST['thankyou_card'])));
    } elseif (isset($_POST['aesir/thankyou_card'])) {
        $order->update_meta_data('thankyou_card', sanitize_text_field(wp_unslash($_POST['aesir/thankyou_card'])));
    } else {
        // Blocks store under _wc_other/<namespace>/<field> — copy to plain meta for admin convenience
        $val = $order->get_meta('_wc_other/aesir/thankyou_card');
        if ($val) {
            $order->update_meta_data('thankyou_card', $val);
        }
    }

    // thankyou_message
    if (isset($_POST['thankyou_message'])) {
        $order->update_meta_data('thankyou_message', sanitize_textarea_field(wp_unslash($_POST['thankyou_message'])));
    } elseif (isset($_POST['aesir/thankyou_message'])) {
        $order->update_meta_data('thankyou_message', sanitize_text_field(wp_unslash($_POST['aesir/thankyou_message'])));
    } else {
        $val = $order->get_meta('_wc_other/aesir/thankyou_message');
        if ($val) {
            $order->update_meta_data('thankyou_message', $val);
        }
    }
}

// 4) Show in admin order meta
add_action('woocommerce_admin_order_data_after_billing_address', 'aesir_display_thankyou_card_admin', 10, 1);
function aesir_display_thankyou_card_admin($order)
{
    $card = $order->get_meta('thankyou_card') ?: $order->get_meta('_wc_other/aesir/thankyou_card');
    $message = $order->get_meta('thankyou_message') ?: $order->get_meta('_wc_other/aesir/thankyou_message');

    if ($card) {
        echo '<p><strong>' . esc_html__('Thank You Card:', 'aesir') . '</strong> ' . esc_html($card) . '</p>';
    }
    if ($message) {
        echo '<p><strong>' . esc_html__('Message:', 'aesir') . '</strong><br>' . nl2br(esc_html($message)) . '</p>';
    }
}

// 5) Optional: include in emails (same approach)
add_action('woocommerce_email_after_order_table', 'aesir_display_thankyou_card_in_email', 10, 4);
function aesir_display_thankyou_card_in_email($order, $sent_to_admin, $plain_text, $email)
{
    $card = $order->get_meta('thankyou_card') ?: $order->get_meta('_wc_other/aesir/thankyou_card');
    $message = $order->get_meta('thankyou_message') ?: $order->get_meta('_wc_other/aesir/thankyou_message');

    if ($card || $message) {
        echo '<h3>' . esc_html__('Thank You Card', 'aesir') . '</h3>';
        if ($card)
            echo '<p><strong>' . esc_html__('Card:', 'aesir') . '</strong> ' . esc_html($card) . '</p>';
        if ($message)
            echo '<p><strong>' . esc_html__('Message:', 'aesir') . '</strong><br>' . nl2br(esc_html($message)) . '</p>';
    }
}
?>