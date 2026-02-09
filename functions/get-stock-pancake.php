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

add_action('wp_ajax_get_pancake_stock', 'ajax_get_pancake_stock');
add_action('wp_ajax_nopriv_get_pancake_stock', 'ajax_get_pancake_stock');

function ajax_get_pancake_stock()
{
  $sku = sanitize_text_field($_POST['sku'] ?? '');
  $display_id = sanitize_text_field($_POST['display_id'] ?? '');

  if (empty($sku)) {
    wp_send_json_error('No SKU provided');
    return;
  }

  $result = get_pancake_stock($sku, $display_id ?: null);

  if ($result === false) {
    wp_send_json_error('No stock data found');
  } else {
    wp_send_json_success($result);
  }
}

add_action('wp_ajax_get_product_variations_stock', 'ajax_get_product_variations_stock');
add_action('wp_ajax_nopriv_get_product_variations_stock', 'ajax_get_product_variations_stock');

function ajax_get_product_variations_stock()
{
  $product_id = intval($_POST['product_id'] ?? 0);
  if (!$product_id)
    wp_send_json_error(['message' => 'Missing product ID']);

  $product = wc_get_product($product_id);
  if (!$product || $product->get_type() !== 'variable')
    wp_send_json_error(['message' => 'Not a variable product']);

  $variations = $product->get_children();
  $result = [];

  foreach ($variations as $variation_id) {
    $variation = wc_get_product($variation_id);
    $sku = $variation->get_sku();
    if (!$sku)
      continue;

    $stock_data = get_pancake_stock($sku);

    $attributes = $variation->get_attributes();

    $result[] = [
      'sku' => $sku,
      'attributes' => $attributes,
      'stock' => $stock_data['stock'] ?? 0,
      'variation_id' => $variation_id,
    ];
  }

  wp_send_json_success($result);
}


/**
 * Get stock quantity from Pancake POS by SKU
 *
 * STRATEGY:
 * 1. First, try the FULL original SKU as-is (e.g., "581-2" as product custom_id)
 * 2. If not found and SKU contains dash, try splitting: base SKU + display_id
 */
function get_pancake_stock($sku, $display_id = null)
{
  $api_key = '9f0e1aa56d604de49c556d6b5d74e151';
  $shop_id = '1720099964';
  $base_url = 'https://pos.pages.fm/api/v1';
  $warehouse_id = '8e83507c-9e7d-45f6-a14f-695a857c0c38';

  $original_sku = $sku;

  error_log("========== PANCAKE STOCK CHECK ==========");
  error_log("INPUT: sku='$sku', display_id='" . ($display_id ?? 'NULL') . "'");

  // **STRATEGY 1: Try FULL SKU as-is first (no splitting)**
  if ($display_id === null || $display_id === '') {
    $result = _pancake_fetch_stock($sku, null, $api_key, $shop_id, $base_url, $warehouse_id);

    if ($result !== false) {
      error_log("STRATEGY 1 SUCCESS: Found stock for full SKU '$sku'");
      return $result;
    }

    error_log("STRATEGY 1 FAILED: No product found for full SKU '$sku'");

    // **STRATEGY 2: If SKU contains dash, try splitting**
    if (strpos($sku, '-') !== false) {
      $parts = explode('-', $sku, 2);
      $base_sku = $parts[0];
      $variation_display_id = $sku;

      error_log("STRATEGY 2: Trying base_sku='$base_sku', display_id='$variation_display_id'");

      $result = _pancake_fetch_stock($base_sku, $variation_display_id, $api_key, $shop_id, $base_url, $warehouse_id);

      if ($result !== false) {
        error_log("STRATEGY 2 SUCCESS: Found stock via base SKU");
        return $result;
      }

      error_log("STRATEGY 2 FAILED: No variation match");
    }

    return false;
  }

  // **If display_id is explicitly provided, use it directly**
  error_log("EXPLICIT display_id provided: sku='$sku', display_id='$display_id'");
  return _pancake_fetch_stock($sku, $display_id, $api_key, $shop_id, $base_url, $warehouse_id);
}

/**
 * Internal function to fetch stock from Pancake API
 */
function _pancake_fetch_stock($sku, $display_id, $api_key, $shop_id, $base_url, $warehouse_id)
{
  $product_url = "{$base_url}/shops/{$shop_id}/products/" . urlencode($sku) . "?api_key={$api_key}";
  error_log("API URL: $product_url");

  $response = wp_remote_get($product_url, ['timeout' => 10]);

  if (is_wp_error($response)) {
    error_log("API Error: " . $response->get_error_message());
    return false;
  }

  $data = json_decode(wp_remote_retrieve_body($response), true);
  if (empty($data['success']) || empty($data['data'])) {
    error_log("No product found for SKU='$sku'");
    return false;
  }

  $product = $data['data'];
  $variations = $product['variations'] ?? [];

  $all_display_ids = array_column($variations, 'display_id');
  error_log("Product '{$product['name']}' has " . count($variations) . " variations: " . json_encode($all_display_ids));

  $variation = null;

  if ($display_id !== null && $display_id !== '') {
    error_log("Searching for display_id='$display_id'");

    foreach ($variations as $var) {
      $var_display_id = trim((string)($var['display_id'] ?? ''));
      $search_trimmed = trim($display_id);

      if ($var_display_id === $search_trimmed) {
        $variation = $var;
        error_log("FOUND MATCH: display_id='$var_display_id'");
        break;
      }
    }

    if ($variation === null) {
      error_log("NO VARIATION MATCH for display_id='$display_id'");
      return false;
    }
  } else {
    if (count($variations) === 1) {
      $variation = $variations[0];
      error_log("Single variation product, using: " . ($variation['display_id'] ?? 'N/A'));
    } elseif (!empty($variations)) {
      foreach ($variations as $var) {
        $var_display_id = trim((string)($var['display_id'] ?? ''));
        if ($var_display_id === $sku) {
          $variation = $var;
          error_log("Found variation matching SKU: $sku");
          break;
        }
      }
      if ($variation === null) {
        $variation = $variations[0];
        error_log("No exact match, using first variation: " . ($variation['display_id'] ?? 'N/A'));
      }
    }
  }

  if ($variation === null) {
    error_log("No variation found in product");
    return false;
  }

  $stock = 0;
  $found_warehouse = false;

  foreach ($variation['variations_warehouses'] ?? [] as $wh) {
    if (($wh['warehouse_id'] ?? '') === $warehouse_id) {
      $stock = (int)($wh['remain_quantity'] ?? 0);
      $found_warehouse = true;
      error_log("WAREHOUSE STOCK: $stock (warehouse: $warehouse_id)");
      break;
    }
  }

  if (!$found_warehouse) {
    $stock = (int)($variation['remain_quantity'] ?? 0);
    error_log("Warehouse not found, using variation total: $stock");
  }

  $result = [
    'stock' => $stock,
    'product_name' => $product['name'] ?? '',
    'id' => $product['id'] ?? '',
    'variation_display_id' => $variation['display_id'] ?? ''
  ];

  error_log("RESULT: " . json_encode($result));

  return $result;
}

add_action('wp_ajax_check_acf_gallery', 'ajax_check_acf_gallery');
add_action('wp_ajax_nopriv_check_acf_gallery', 'ajax_check_acf_gallery');
function ajax_check_acf_gallery()
{
  $product_id = intval($_POST['product_id'] ?? 0);
  if (!$product_id)
    wp_send_json_error(['message' => 'Missing product ID']);

  $gallery = get_field('gallery', $product_id);
  if ($gallery && is_array($gallery) && count($gallery) > 0) {
    foreach ($gallery as $item) {
      if (!$item['is_video']) {
        $image_id = $item['image'];
        $image_url = wp_get_attachment_url($image_id);
        if ($image_url) {
          wp_send_json_success(['image_url' => $image_url]);
        }
      }
    }
  }

  wp_send_json_error(['message' => 'No ACF gallery found']);
}

add_action('woocommerce_checkout_process', 'aesir_validate_pancake_stock_before_checkout');
function aesir_validate_pancake_stock_before_checkout() {
  if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

  $errors = [];

  foreach ( WC()->cart->get_cart() as $cart_item ) {
    $product = $cart_item['data'];
    $qty = intval( $cart_item['quantity'] );
    if ( ! $product ) continue;

    $sku = $product->get_sku();
    if ( empty( $sku ) && $product->is_type( 'variation' ) ) {
      $parent = wc_get_product( $product->get_parent_id() );
      $sku = $parent ? $parent->get_sku() : '';
    }
    if ( empty( $sku ) ) continue;

    $stock_data = get_pancake_stock( $sku, null );

    if ( $stock_data === false || ! isset( $stock_data['stock'] ) ) {
      $errors[] = sprintf( '%s (SKU: %s) could not be verified. Please try again or contact support.', $product->get_name(), $sku );
      continue;
    }

    $available = intval( $stock_data['stock'] );
    if ( $available < $qty ) {
      if ( $available <= 0 ) {
        $errors[] = sprintf( '%s (SKU: %s) is out of stock. Please remove it from your cart.', $product->get_name(), $sku );
      } else {
        $errors[] = sprintf( 'Only %d left in stock for %s (SKU: %s). You have %d in your cart.', $available, $product->get_name(), $sku, $qty );
      }
    }
  }

  if ( ! empty( $errors ) ) {
    foreach ( $errors as $err ) {
      wc_add_notice( $err, 'error' );
    }
  }
}

?>
