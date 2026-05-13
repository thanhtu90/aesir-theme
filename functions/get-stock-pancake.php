<?php

add_action('wp_footer', function () {
  if (is_product()): ?>
    <script type="text/javascript">
      jQuery(function ($) {
        const stockInfo = $('#pancake-stock-info');

        // cache and pending requests to avoid duplicate AJAX calls per SKU
        const pancakeStockCache = {};
        const pancakePending = {};

        // When variation is found (size + color selected)
        $('form.variations_form').on('found_variation', function (e, variation) {
          if (variation && variation.sku) {
            const sku = variation.sku;
            stockInfo.text('Checking stock for SKU: ' + sku + ' ...');

            // put the Add to cart into a loading state until the stock check finishes
            const addBtn = $('form.variations_form').find('.single_add_to_cart_button');
            addBtn.prop('disabled', true)
              .attr('aria-disabled', 'true')
              .attr('aria-busy', 'true')
              .addClass('disabled loading')
              .css('opacity', '.5');

            // preserve original text so handlers can restore if needed
            if (typeof addBtn.data('orig-text') === 'undefined') {
              addBtn.data('orig-text', addBtn.text());
            }
            addBtn.text('Checking...');

            // Use cached result if available
            if (Object.prototype.hasOwnProperty.call(pancakeStockCache, sku)) {
              const data = pancakeStockCache[sku];
              if (data.stock <= 0) {
                addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
              } else {
                addBtn.prop('disabled', false).attr('aria-disabled', 'false').attr('aria-busy', 'false').removeClass('disabled loading').css('opacity', '1').text('Add to cart');
              }
              if (data.stock <= 5) {
                stockInfo.html(`<b>Available stock:</b> ${data.stock}`);
              } else {
                stockInfo.text('');
              }
              return;
            }

            // If a request for this SKU is already pending, attach handlers to it
            if (pancakePending[sku]) {
              pancakePending[sku].done(function (response) {
                if (response && response.success) {
                  console.log('Pending response for', sku, response);
                  pancakeStockCache[sku] = response.data;
                  if (response.data.stock <= 0) {
                    addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                  } else {
                    addBtn.prop('disabled', false).attr('aria-disabled', 'false').attr('aria-busy', 'false').removeClass('disabled loading').css('opacity', '1').text('Add to cart');
                  }
                  if (response.data.stock <= 5) {
                    stockInfo.html(`<b>Available stock:</b> ${response.data.stock}`);
                  } else {
                    stockInfo.text('');
                  }
                } else {
                  stockInfo.text('Unable to fetch stock.');
                  addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                }
              }).fail(function () {
                stockInfo.text('Error connecting to Pancake.');
                addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
              });
              return;
            }

            // **FIRST CALL: Original SKU + display_id=null** (works for 576-2)
            pancakePending[sku] = $.ajax({
              url: '<?php echo admin_url("admin-ajax.php"); ?>',
              method: 'POST',
              data: {
                action: 'get_pancake_stock',
                sku: sku,      // Keep original SKU unchanged
                display_id: null
              }
            })
              .done(function (response) {
                delete pancakePending[sku];
                console.log(JSON.stringify(response));

                // **CRITICAL FIX: Check if we actually got stock data**
                if (response && response.success && response.data && response.data.stock !== undefined) {

                  console.log('Primary response for', sku, response);
                  // SUCCESS: Cache and use result
                  pancakeStockCache[sku] = response.data;
                  if (response.data.stock <= 0) {
                    addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').css('opacity', '.5').removeClass('loading').text('Unavailable');
                  } else {
                    addBtn.prop('disabled', false).attr('aria-disabled', 'false').attr('aria-busy', 'false').removeClass('disabled loading').css('opacity', '1').text('Add to cart');
                  }
                  if (response.data.stock <= 5) {
                    stockInfo.html(`<b>Available stock:</b> ${response.data.stock}`);
                  } else {
                    stockInfo.text('');
                  }
                } else {
                  // **FALLBACK TRIGGER: First call failed -> try base SKU**
                  console.log('First call failed for', sku, '-> trying fallback');

                  if (typeof sku === 'string' && sku.indexOf('-') !== -1) {
                    const baseSku = sku.split('-')[0];

                    if (!Object.prototype.hasOwnProperty.call(pancakeStockCache, baseSku) && !pancakePending[baseSku]) {
                      stockInfo.text('Trying base SKU: ' + baseSku + ' ...');

                      pancakePending[baseSku] = $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        method: 'POST',
                        data: {
                          action: 'get_pancake_stock',
                          sku: baseSku,  // "582"
                          display_id: sku   // "582-1"
                        }
                      })
                        .done(function (response) {
                          console.log('11111');
                          console.log(JSON.stringify(response));
                          delete pancakePending[baseSku];
                          if (response && response.success && response.data && response.data.stock !== undefined) {
                            console.log('Fallback response for', baseSku, sku, response);
                            pancakeStockCache[sku] = response.data;
                            if (response.data.stock <= 0) {
                              addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                            } else {
                              addBtn.prop('disabled', false).attr('aria-disabled', 'false').attr('aria-busy', 'false').removeClass('disabled loading').css('opacity', '1').text('Add to cart');
                            }
                            if (response.data.stock <= 5) {
                              stockInfo.html(`<b>Available stock:</b> ${response.data.stock}`);
                            } else {
                              stockInfo.text('');
                            }
                          } else {
                            stockInfo.text('Unable to fetch stock.');
                            addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                          }
                        })
                        .fail(function () {
                          console.log('2222');
                          delete pancakePending[baseSku];
                          stockInfo.text('Error connecting to Pancake.');
                          addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                        });
                      return; // Prevent further execution
                    }
                  }
                  // No fallback possible
                  stockInfo.text('Unable to fetch stock.');
                  addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                }
              })
              .fail(function () {
                delete pancakePending[sku];
                // On network error, also try fallback
                if (typeof sku === 'string' && sku.indexOf('-') !== -1) {
                  const baseSku = sku.split('-')[0];
                  // ... same fallback logic as above
                  stockInfo.text('Network error - trying fallback...');
                  // (same fallback code as in .done failure case)
                } else {
                  stockInfo.text('Error connecting to Pancake.');
                  addBtn.prop('disabled', true).attr('aria-disabled', 'true').attr('aria-busy', 'false').addClass('disabled').removeClass('loading').css('opacity', '.5').text('Unavailable');
                }
              });
          } else {
            stockInfo.text('');
          }
        });

        // Reset when variation is reset
        $('form.variations_form').on('reset_data', function () {
          stockInfo.text('');
        });
      });
    </script>

  <?php endif; ?>
  <script type="text/javascript">
    jQuery(function ($) {
      const variationCache = {};
      const galleryCache = {};

      $('.product').hover(
        function () {
          const productEl = $(this);
          const infoBox = productEl.find('.product-variations-info');
          const productId = productEl.data('product_id');

          if (!productId) return;

          infoBox.css('visibility', 'visible').text('Checking variations...');

          // Save original image attributes (only once)
          const img = productEl.find('img').first();
          if (img.length && typeof productEl.data('original-src') === 'undefined') {
            productEl.data('original-src', img.attr('src') || '');
            productEl.data('original-srcset', img.attr('srcset') || '');
            productEl.data('original-data-src', img.attr('data-src') || '');
            productEl.data('original-data-srcset', img.attr('data-srcset') || '');
          }

          // Use cached variation data if exists
          if (variationCache[productId]) {
            renderVariations(variationCache[productId], infoBox);
          } else {
            $.ajax({
              url: '<?php echo admin_url("admin-ajax.php"); ?>',
              type: 'POST',
              data: {
                action: 'get_product_variations_stock',
                product_id: productId
              },
              success: function (response) {
                if (response.success) {
                  variationCache[productId] = response.data;
                  renderVariations(response.data, infoBox);
                } else {
                  infoBox.text('No variations found');
                }
              },
              error: function () {
                infoBox.text('Error fetching variations');
              }
            });
          }

          // Change product image from ACF gallery (with caching)
          if (galleryCache[productId]) {
            swapImage(img, galleryCache[productId]);
          } else {
            $.ajax({
              url: '<?php echo admin_url("admin-ajax.php"); ?>',
              type: 'POST',
              data: {
                action: 'check_acf_gallery',
                product_id: productId
              },
              success: function (resp) {
                if (resp && resp.success && resp.data && resp.data.image_url) {
                  galleryCache[productId] = resp.data.image_url;
                  swapImage(img, resp.data.image_url);
                }
              }
            });
          }
        },
        function () {
          const productEl = $(this);
          productEl.find('.product-variations-info').css('visibility', 'hidden');

          // Restore original image on hover out
          const img = productEl.find('img').first();
          if (img.length) {
            const orig = productEl.data('original-src') || '';
            const origSrcset = productEl.data('original-srcset') || '';
            const origDataSrc = productEl.data('original-data-src') || '';
            const origDataSrcset = productEl.data('original-data-srcset') || '';

            if (orig) img.attr('src', orig);
            if (origSrcset) img.attr('srcset', origSrcset);
            if (origDataSrc) img.attr('data-src', origDataSrc);
            if (origDataSrcset) img.attr('data-srcset', origDataSrcset);
          }
        }
      );

      // Render variation list
      function renderVariations(data, infoBox) {
        let html =
          '<ul class="notranslate" style="list-style:none; padding:0; margin:0; display:flex; align-items:center; gap:4px; justify-content:center;">';
        data.forEach((v) => {
          if (v.stock > 0) {
            html += `<li style="border:1px solid black; padding:3px 7px; border-radius:3px; text-transform:uppercase">${v.attributes.size || v.attributes.pa_size || 'N/A'}</li>`;
          }
        });
        html += '</ul>';
        infoBox.html(html);
      }

      // Swap image helper
      function swapImage(img, newUrl) {
        if (!img || !newUrl) return;
        img.attr('src', newUrl);
        img.removeAttr('srcset data-src data-srcset');
      }
    });
  </script>
  <?php
});
