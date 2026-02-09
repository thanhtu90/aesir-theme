/**
 * Checkout Payment Methods Handler
 * Controls visibility of payment methods based on country
 * Version: 2.0
 */
(function($) {
    'use strict';

    // Payment method values
    var COD_VALUE = 'cod';
    var PAYPAL_VALUE = 'ppcp-gateway';
    var BANK_VALUE = 'bacs';

    /**
     * Get elements for a payment method
     */
    function getElems(value) {
        var $input = $('input[type="radio"][value="' + value + '"]');
        if (!$input.length) return null;

        var $label = $input.closest('.wc-block-components-radio-control__option, .wc-block-components-radio-control-accordion-option, label');
        var $content = $();
        var id = $input.attr('id');

        if (id) {
            $content = $('#' + id + '__content');
        }

        if (!$content.length && $label.length) {
            $content = $label.next('.wc-block-components-radio-control-accordion-content, .wc-block-components-radio-control__content');
        }

        return { input: $input, label: $label, content: $content };
    }

    /**
     * Hide a payment method
     */
    function hideMethod(value) {
        var e = getElems(value);
        if (!e) return;

        if (e.label) e.label.css('display', 'none');
        if (e.content) e.content.css('display', 'none');

        if (e.input.prop('checked')) {
            e.input.prop('checked', false).trigger('change');
            selectFirstVisible();
        }
    }

    /**
     * Show a payment method
     */
    function showMethod(value) {
        var e = getElems(value);
        if (!e) return;

        if (e.label) e.label.css('display', '');
        if (e.content) e.content.css('display', '');
    }

    /**
     * Select first visible payment method
     */
    function selectFirstVisible() {
        var $visible = $('input[type="radio"][value]').filter(function() {
            var $l = $(this).closest('.wc-block-components-radio-control__option, .wc-block-components-radio-control-accordion-option, label');
            return $l.length && $l.is(':visible');
        });

        if ($visible.length) {
            $visible.first().prop('checked', true).trigger('change');
        }
    }

    /**
     * Apply payment method rules based on country
     */
    function applyForCountry(country) {
        if (!country) return;

        country = String(country).toUpperCase();

        if (country === 'VN' || country === 'VN_VN' || country === 'VNM') {
            // Vietnam: show COD and Bank, hide PayPal
            showMethod(COD_VALUE);
            showMethod(BANK_VALUE);
            hideMethod(PAYPAL_VALUE);
        } else {
            // International: hide COD and Bank, show PayPal
            hideMethod(COD_VALUE);
            hideMethod(BANK_VALUE);
            showMethod(PAYPAL_VALUE);
        }
    }

    /**
     * Read country from select fields
     */
    function readCountryFromSelect() {
        return $('#shipping-country, select#shipping_country, select[name="shipping_country"], #billing-country, select#billing_country, select[name="billing_country"]')
            .filter(':visible')
            .first()
            .val();
    }

    /**
     * Re-apply rules
     */
    function reapply() {
        var country = readCountryFromSelect();
        applyForCountry(country);
    }

    // Initialize when DOM is ready
    $(function() {
        // Country change handler
        $(document).on('change', '#shipping-country, select#shipping_country, select[name="shipping_country"], #billing-country, select#billing_country, select[name="billing_country"]', function() {
            applyForCountry($(this).val());
        });

        // WooCommerce checkout update
        $(document.body).on('updated_checkout', reapply);

        // MutationObserver for block checkout
        var paymentSelectors = '.wc-block-components-checkout__payment, .wc-block-checkout__payment, .wc-block-components-payment-methods, .wc-block-components-payments';
        var payArea = document.querySelector(paymentSelectors);

        if (payArea) {
            var mo = new MutationObserver(function() {
                reapply();
            });
            mo.observe(payArea, { childList: true, subtree: true });
        }

        // Fallback polling
        var tries = 0;
        var poll = setInterval(function() {
            reapply();
            tries++;
            if (tries > 8) clearInterval(poll);
        }, 500);

        // Initial run
        setTimeout(reapply, 700);
    });

})(jQuery);
