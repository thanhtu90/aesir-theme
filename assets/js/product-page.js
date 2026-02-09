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
        var stockInfo = $('#pancake-stock-info');
        var pancakeStockCache = {};
        var pancakePending = {};

        // When variation is found
        $('form.variations_form').on('found_variation', function(e, variation) {
            if (variation && variation.sku) {
                var sku = variation.sku;
                stockInfo.text('Checking stock...');

                var addBtn = $('form.variations_form').find('.single_add_to_cart_button');
                addBtn.prop('disabled', true)
                    .addClass('disabled loading')
                    .css('opacity', '.5');

                if (typeof addBtn.data('orig-text') === 'undefined') {
                    addBtn.data('orig-text', addBtn.text());
                }
                addBtn.text('Checking...');

                // Use cached result if available
                if (pancakeStockCache.hasOwnProperty(sku)) {
                    handleStockResult(pancakeStockCache[sku], addBtn, stockInfo);
                    return;
                }

                // If request pending, wait for it
                if (pancakePending[sku]) {
                    pancakePending[sku].done(function(response) {
                        if (response && response.success) {
                            pancakeStockCache[sku] = response.data;
                            handleStockResult(response.data, addBtn, stockInfo);
                        } else {
                            handleStockError(addBtn, stockInfo);
                        }
                    }).fail(function() {
                        handleStockError(addBtn, stockInfo);
                    });
                    return;
                }

                // Make AJAX request
                pancakePending[sku] = $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'get_pancake_stock',
                        sku: sku,
                        display_id: null
                    }
                }).done(function(response) {
                    delete pancakePending[sku];

                    if (response && response.success && response.data && response.data.stock !== undefined) {
                        pancakeStockCache[sku] = response.data;
                        handleStockResult(response.data, addBtn, stockInfo);
                    } else {
                        // Fallback: try base SKU
                        tryFallbackSku(sku, addBtn, stockInfo);
                    }
                }).fail(function() {
                    delete pancakePending[sku];
                    handleStockError(addBtn, stockInfo);
                });
            } else {
                stockInfo.text('');
            }
        });

        // Reset on variation clear
        $('form.variations_form').on('reset_data', function() {
            stockInfo.text('');
        });

        function handleStockResult(data, addBtn, stockInfo) {
            if (data.stock <= 0) {
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

            if (data.stock > 0 && data.stock <= 5) {
                stockInfo.html('<b>Available stock:</b> ' + data.stock);
            } else {
                stockInfo.text('');
            }
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
                    if (response && response.success && response.data && response.data.stock !== undefined) {
                        pancakeStockCache[sku] = response.data;
                        handleStockResult(response.data, addBtn, stockInfo);
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
