/**
 * Product Page Scripts
 * Handles stock checking, image swapping, sticky sidebar
 * Version: 2.0
 */
(function($) {
    'use strict';

    // Get AJAX URL from localized script
    var ajaxUrl = (typeof aesirAjax !== 'undefined') ? aesirAjax.ajaxUrl : '/wp-admin/admin-ajax.php';

    // ============================================================
    // STOCK CHECKING (Single Product Page)
    // ============================================================
    $(function() {
        var pancakeStockCache = {};
        var pancakePending = {};

        function mergeStockData(data, stockVal) {
            return $.extend({}, data, { stock: stockVal });
        }

        /** Read variation array from form — jQuery .data() key differs by WC/jQuery version. */
        function getProductVariations($form) {
            var v = $form.data('product_variations');
            if (!v) {
                v = $form.data('productVariations');
            }
            if (!v) {
                var raw = $form.attr('data-product_variations');
                if (raw) {
                    try {
                        v = JSON.parse(raw);
                    } catch (err) {
                        v = null;
                    }
                }
            }
            return v && v.length ? v : null;
        }

        /** WooCommerce sometimes omits variation.sku; resolve from embedded variations data. */
        function resolveVariationSku(variation, $form) {
            var s = variation && variation.sku != null ? String(variation.sku).trim() : '';
            if (s) return s;
            var vid = variation && variation.variation_id;
            if (!vid || !$form || !$form.length) return '';
            var list = getProductVariations($form);
            if (!list) return '';
            for (var i = 0; i < list.length; i++) {
                if (String(list[i].variation_id) === String(vid)) {
                    var sk = list[i].sku != null ? String(list[i].sku).trim() : '';
                    return sk || '';
                }
            }
            return '';
        }

        function ensureStockInfo($form) {
            var $el = $form.find('#pancake-stock-info');
            if ($el.length) return $el;
            $el = $('#pancake-stock-info');
            if ($el.length) return $el;
            $el = $('<div/>', {
                id: 'pancake-stock-info',
                class: 'aesir-pancake-stock-line',
                attr: { 'aria-live': 'polite' }
            });
            var $wrap = $form.find('.woocommerce-variation-add-to-cart').first();
            if ($wrap.length) {
                $wrap.append($el);
            } else {
                $form.append($el);
            }
            return $el;
        }

        function parseStockValue(data) {
            if (!data || data.stock === undefined || data.stock === null) return NaN;
            var v = data.stock;
            if (typeof v === 'string') v = parseInt(v, 10);
            var n = Number(v);
            return isNaN(n) ? NaN : n;
        }

        /**
         * WooCommerce fires found_variation before it replaces .single_variation HTML (see onFoundVariation + 300ms timeout).
         * Our mount node from PHP was inside that block — it gets destroyed; stock updates never appear.
         * show_variation runs on .single_variation after the new markup exists (and bubbles to the form).
         */
        $(document.body).on('show_variation', 'form.variations_form', function(e, variation, purchasable) {
            var $form = $(this);
            var stockInfo = ensureStockInfo($form);
            var sku = resolveVariationSku(variation, $form);

            if (!sku) {
                stockInfo.text('');
                return;
            }

            stockInfo.text('Checking stock...');

            var addBtn = $form.find('.single_add_to_cart_button');
            addBtn.prop('disabled', true)
                .addClass('disabled loading')
                .css('opacity', '.5');

            if (typeof addBtn.data('orig-text') === 'undefined') {
                addBtn.data('orig-text', addBtn.text());
            }
            addBtn.text('Checking...');

            if (Object.prototype.hasOwnProperty.call(pancakeStockCache, sku)) {
                handleStockResult(pancakeStockCache[sku], addBtn, stockInfo);
                return;
            }

            if (pancakePending[sku]) {
                pancakePending[sku].done(function(response) {
                    var data = response && response.data;
                    var n = parseStockValue(data || {});
                    if (response && response.success && data && !isNaN(n)) {
                        data = mergeStockData(data, n);
                        pancakeStockCache[sku] = data;
                        handleStockResult(data, addBtn, stockInfo);
                    } else {
                        handleStockError(addBtn, stockInfo);
                    }
                }).fail(function() {
                    handleStockError(addBtn, stockInfo);
                });
                return;
            }

            pancakePending[sku] = $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: {
                    action: 'get_pancake_stock',
                    sku: sku
                }
            }).done(function(response) {
                delete pancakePending[sku];

                var data = response && response.data;
                var n = parseStockValue(data || {});
                if (response && response.success && data && !isNaN(n)) {
                    data = mergeStockData(data, n);
                    pancakeStockCache[sku] = data;
                    handleStockResult(data, addBtn, stockInfo);
                } else {
                    tryFallbackSku(sku, addBtn, stockInfo);
                }
            }).fail(function() {
                delete pancakePending[sku];
                handleStockError(addBtn, stockInfo);
            });
        });

        $(document.body).on('reset_data', 'form.variations_form', function() {
            $(this).find('#pancake-stock-info').text('');
        });

        $(document.body).on('hide_variation', 'form.variations_form', function() {
            $(this).find('#pancake-stock-info').text('');
        });

        function handleStockResult(data, addBtn, stockInfo) {
            var n = parseStockValue(data);
            if (isNaN(n)) n = 0;

            if (n <= 0) {
                addBtn.prop('disabled', true)
                    .addClass('disabled')
                    .removeClass('loading')
                    .css('opacity', '.5')
                    .text('Unavailable');
            } else {
                addBtn.prop('disabled', false)
                    .removeClass('disabled loading')
                    .css('opacity', '1')
                    .text('Add to cart');
            }

            stockInfo.html(
                '<span class="aesir-pancake-stock-label">Available stock:</span>' +
                '<span class="aesir-pancake-stock-qty">' + n + '</span>'
            );
        }

        function handleStockError(addBtn, stockInfo) {
            stockInfo.text('Unable to verify stock');
            addBtn.prop('disabled', true)
                .addClass('disabled')
                .removeClass('loading')
                .css('opacity', '.5')
                .text('Unavailable');
        }

        function tryFallbackSku(sku, addBtn, stockInfo) {
            if (typeof sku === 'string' && sku.indexOf('-') !== -1) {
                var baseSku = sku.split('-')[0];

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'get_pancake_stock',
                        sku: baseSku,
                        display_id: sku
                    }
                }).done(function(response) {
                    var data = response && response.data;
                    var n = parseStockValue(data || {});
                    if (response && response.success && data && !isNaN(n)) {
                        data = mergeStockData(data, n);
                        pancakeStockCache[sku] = data;
                        handleStockResult(data, addBtn, stockInfo);
                    } else {
                        handleStockError(addBtn, stockInfo);
                    }
                }).fail(function() {
                    handleStockError(addBtn, stockInfo);
                });
            } else {
                handleStockError(addBtn, stockInfo);
            }
        }
    });

    // ============================================================
    // PRODUCT CARD HOVER (Shop/Archive Pages)
    // ============================================================
    $(function() {
        var variationCache = {};
        var galleryCache = {};

        $('.product').hover(
            function() {
                var productEl = $(this);
                var infoBox = productEl.find('.product-variations-info');
                var productId = productEl.data('product_id');

                if (!productId) return;

                infoBox.css('visibility', 'visible').text('Loading...');

                // Save original image
                var img = productEl.find('img').first();
                if (img.length && typeof productEl.data('original-src') === 'undefined') {
                    productEl.data('original-src', img.attr('src') || '');
                    productEl.data('original-srcset', img.attr('srcset') || '');
                }

                // Get variations from cache or AJAX
                if (variationCache[productId]) {
                    renderVariations(variationCache[productId], infoBox);
                } else {
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'get_product_variations_stock',
                            product_id: productId
                        },
                        success: function(response) {
                            if (response.success) {
                                variationCache[productId] = response.data;
                                renderVariations(response.data, infoBox);
                            } else {
                                infoBox.text('');
                            }
                        },
                        error: function() {
                            infoBox.text('');
                        }
                    });
                }

                // Swap image from ACF gallery
                if (galleryCache[productId]) {
                    swapImage(img, galleryCache[productId]);
                } else {
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'check_acf_gallery',
                            product_id: productId
                        },
                        success: function(resp) {
                            if (resp && resp.success && resp.data && resp.data.image_url) {
                                galleryCache[productId] = resp.data.image_url;
                                swapImage(img, resp.data.image_url);
                            }
                        }
                    });
                }
            },
            function() {
                var productEl = $(this);
                productEl.find('.product-variations-info').css('visibility', 'hidden');

                // Restore original image
                var img = productEl.find('img').first();
                if (img.length) {
                    var orig = productEl.data('original-src') || '';
                    var origSrcset = productEl.data('original-srcset') || '';

                    if (orig) img.attr('src', orig);
                    if (origSrcset) img.attr('srcset', origSrcset);
                }
            }
        );

        function renderVariations(data, infoBox) {
            var html = '<ul class="notranslate" style="list-style:none; padding:0; margin:0; display:flex; align-items:center; gap:4px; justify-content:center;">';

            data.forEach(function(v) {
                if (v.stock > 0) {
                    var size = v.attributes.size || v.attributes.pa_size || 'N/A';
                    html += '<li style="border:1px solid black; padding:3px 7px; border-radius:3px; text-transform:uppercase">' + size + '</li>';
                }
            });

            html += '</ul>';
            infoBox.html(html);
        }

        function swapImage(img, newUrl) {
            if (!img || !newUrl) return;
            img.attr('src', newUrl);
            img.removeAttr('srcset data-src data-srcset');
        }
    });

    // ============================================================
    // STICKY SIDEBAR (Product Page)
    // ============================================================
    $(function() {
        var $window = $(window);
        var $wrapper = $('#product-single-wrapper');
        var $left = $('#product-single-left');
        var $info = $('#product-single-info');

        if (!$wrapper.length || !$info.length) return;

        $wrapper.css('position', 'relative');

        function updateSticky() {
            var scrollTop = $window.scrollTop();
            var winH = $window.height();
            var winW = $window.width();
            var topOffset = 45;

            // Mobile: reset
            if (winW < 1024) {
                $left.css('width', '100%');
                $info.css({
                    width: '100%',
                    position: 'relative',
                    top: '',
                    left: '',
                    transform: '',
                    zIndex: ''
                });
                return;
            }

            var wrapperTop = $wrapper.offset().top;
            var wrapperH = $wrapper.outerHeight();
            var wrapperBottom = wrapperTop + wrapperH;
            var infoH = $info.outerHeight();
            var wrapperW = $wrapper.width();
            var halfW = wrapperW / 2;

            $left.css('width', halfW);
            $info.css('width', halfW);

            var avail = Math.max(0, winH - topOffset);
            var maxShift = Math.max(0, infoH - avail);
            var start = wrapperTop;
            var endShiftPoint = wrapperTop + maxShift;
            var pinBottomPoint = Math.max(wrapperBottom - infoH, wrapperTop);

            if (infoH <= avail) {
                var stopPoint = wrapperBottom - infoH;
                if (scrollTop < wrapperTop) {
                    $info.css({ position: 'relative', top: '', left: '', transform: '', zIndex: '' });
                } else if (scrollTop >= wrapperTop && scrollTop < stopPoint) {
                    $info.css({
                        position: 'fixed',
                        top: topOffset + 'px',
                        left: $wrapper.offset().left + halfW,
                        transform: 'translateX(0)',
                        zIndex: 99
                    });
                } else {
                    $info.css({
                        position: 'absolute',
                        top: wrapperH - infoH,
                        left: '50%',
                        transform: 'translateX(0)',
                        zIndex: ''
                    });
                }
                return;
            }

            if (scrollTop < start) {
                $info.css({ position: 'relative', top: '', left: '', transform: '', zIndex: '' });
            } else if (scrollTop >= start && scrollTop < endShiftPoint && scrollTop < pinBottomPoint) {
                var progress = scrollTop - start;
                var shift = Math.min(maxShift, Math.max(0, progress));
                $info.css({
                    position: 'fixed',
                    top: topOffset + 'px',
                    left: $wrapper.offset().left + halfW,
                    zIndex: 99,
                    transform: 'translateX(0) translateY(' + (-shift) + 'px)'
                });
            } else if (scrollTop >= endShiftPoint && scrollTop < pinBottomPoint) {
                $info.css({
                    position: 'fixed',
                    top: topOffset + 'px',
                    left: $wrapper.offset().left + halfW,
                    zIndex: 99,
                    transform: 'translateX(0) translateY(' + (-maxShift) + 'px)'
                });
            } else {
                $info.css({
                    position: 'absolute',
                    top: wrapperH - infoH,
                    left: '50%',
                    transform: 'translateX(0)',
                    zIndex: ''
                });
            }
        }

        $window.on('scroll resize load', updateSticky);
        updateSticky();
    });

})(jQuery);
