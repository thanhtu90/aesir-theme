<?php
/**
 * Shop/archive UI: variation pills + ACF gallery on product card hover.
 * Single-product Pancake stock lives in assets/js/product-page.js (one handler, no duplicate AJAX).
 */

add_action('wp_footer', function () {
  ?>
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
