<?php
/**
 * Force base store currency (EUR) when PayPal is selected
 * Uses the Price Based on Country plugin's function to reset to base zone
 */

// Detect when PayPal is selected and trigger checkout update
add_action('woocommerce_review_order_before_payment', function() {
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        var lastPaymentMethod = '';
        
        function checkPaymentMethod() {
            var selectedMethod = $('input[name="payment_method"]:checked').val();
            
            // Check if PayPal is selected
            var isPayPal = selectedMethod && (
                selectedMethod.indexOf('paypal') !== -1 || 
                selectedMethod.indexOf('ppcp') !== -1
            );
            
            // Only trigger update if payment method changed
            if (selectedMethod !== lastPaymentMethod) {
                lastPaymentMethod = selectedMethod;
                
                if (isPayPal) {
                    $('body').addClass('paypal-checkout-active');
                } else {
                    $('body').removeClass('paypal-checkout-active');
                }
                
                // Trigger checkout update to recalculate with proper currency
                $(document.body).trigger('update_checkout');
            }
        }
        
        checkPaymentMethod();
        
        $(document.body).on('change', 'input[name="payment_method"]', function() {
            checkPaymentMethod();
        });
    });
    </script>
    <?php
}, 10);

// Reset to base zone (no pricing zone) when PayPal is selected
add_action('woocommerce_checkout_update_order_review', function($post_data) {
    parse_str($post_data, $data);
    
    $chosen_method = isset($data['payment_method']) ? $data['payment_method'] : WC()->session->get('chosen_payment_method');
    
    // Check if PayPal is selected
    $is_paypal = $chosen_method && (
        strpos($chosen_method, 'paypal') !== false || 
        strpos($chosen_method, 'ppcp') !== false
    );
    
    if ($is_paypal) {
        // Store original zone for later restoration
        if (function_exists('wcpbc_the_zone') && wcpbc_the_zone()) {
            WC()->session->set('wcpbc_original_zone_id', wcpbc_the_zone()->get_id());
        }
        
        // Reset current zone to null (will use base/default pricing and currency)
        if (function_exists('wcpbc') && wcpbc()) {
            wcpbc()->current_zone = null;
        }
        
        // Disable WCPBC frontend pricing
        if (class_exists('WCPBC_Frontend_Pricing')) {
            WCPBC_Frontend_Pricing::unset();
        }
    } else {
        // Restore original zone when switching away from PayPal
        $original_zone_id = WC()->session->get('wcpbc_original_zone_id');
        if ($original_zone_id && class_exists('WCPBC_Pricing_Zones')) {
            $zone = WCPBC_Pricing_Zones::get_zone($original_zone_id);
            if ($zone && function_exists('wcpbc') && wcpbc()) {
                wcpbc()->current_zone = $zone;
                
                // Re-enable WCPBC frontend pricing
                if (class_exists('WCPBC_Frontend_Pricing')) {
                    WCPBC_Frontend_Pricing::init();
                }
            }
        }
    }
}, 5);

// Ensure base currency is used for PayPal checkout
add_action('woocommerce_checkout_process', function() {
    $chosen_method = WC()->session->get('chosen_payment_method');
    
    $is_paypal = $chosen_method && (
        strpos($chosen_method, 'paypal') !== false || 
        strpos($chosen_method, 'ppcp') !== false
    );
    
    if ($is_paypal) {
        // Ensure zone is null (base pricing)
        if (function_exists('wcpbc') && wcpbc()) {
            wcpbc()->current_zone = null;
        }
        
        // Force base currency through filter
        add_filter('woocommerce_currency', function($currency) {
            return wcpbc_get_base_currency();
        }, 9999);
    }
}, 5);

// Restore zone after order is completed
add_action('woocommerce_thankyou', function($order_id) {
    $original_zone_id = WC()->session->get('wcpbc_original_zone_id');
    
    if ($original_zone_id && class_exists('WCPBC_Pricing_Zones')) {
        $zone = WCPBC_Pricing_Zones::get_zone($original_zone_id);
        if ($zone && function_exists('wcpbc') && wcpbc()) {
            wcpbc()->current_zone = $zone;
            
            // Re-enable WCPBC pricing
            if (class_exists('WCPBC_Frontend_Pricing')) {
                WCPBC_Frontend_Pricing::init();
            }
        }
        
        // Clean up session
        WC()->session->set('wcpbc_original_zone_id', null);
    }
}, 10);
