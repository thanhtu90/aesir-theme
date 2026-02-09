<?php
add_action('wp_footer', function () {
    if (is_product()):
?>
<script>
    jQuery(function ($) {
        const SELECTORS = 'select#size, select[name="attribute_size"], select[name="attribute_pa_size"]';
        let maxAttempts = 30, attempt = 0, intervalMs = 200;

        // Helper to get cookie value by name
        function getCookie(name) {
            let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }

        // Try to get suggested size from cookie
        let suggestedSize = null;
        let ssdData = getCookie('ssd_data');
        if (ssdData) {
            try {
                let ssdObj = JSON.parse(ssdData);
                if (ssdObj && ssdObj.size) {
                    suggestedSize = String(ssdObj.size).toLowerCase();
                }
            } catch (e) { }
        }

        // Utility: strip trailing " (suggested)" from option text
        function stripSuggestedMarker(text) {
            return String(text || '').replace(/\s*\(suggested\)\s*$/i, '').trim();
        }

        // Add our Suggest Size option safely and mark/unmark matching options
        function addOptionTo($sel) {
            if (!$sel || !$sel.length) return false;

            // Remove outdated "(suggested)" markers first if suggestedSize changed/absent
            $sel.find('option').each(function () {
                let $o = $(this);
                // don't touch our custom option text
                if (this.value === 'suggest-size') return;
                // If there's no suggestedSize or this option does not match it, ensure marker removed
                let currentText = $o.text();
                if (!suggestedSize) {
                    if (/\(suggested\)\s*$/i.test(currentText)) {
                        $o.text(stripSuggestedMarker(currentText));
                    }
                    return;
                }

                // Determine tokens for matching
                let val = String(this.value || '').trim().toLowerCase();
                let txt = String(currentText || '').trim().toLowerCase();
                let valParts = val.split(/[^a-z0-9]+/).filter(Boolean);
                let txtParts = txt.split(/[^a-z0-9]+/).filter(Boolean);
                let matches = (valParts.indexOf(suggestedSize) !== -1 || txtParts.indexOf(suggestedSize) !== -1);

                if (matches) {
                    // append marker if missing
                    if (!/\(suggested\)\s*$/i.test(currentText)) {
                        $o.text(currentText + ' (suggested)');
                    }
                } else {
                    // remove marker if present but no longer matches
                    if (/\(suggested\)\s*$/i.test(currentText)) {
                        $o.text(stripSuggestedMarker(currentText));
                    }
                }
            });

            // Avoid duplicate custom option
            if ($sel.find('option[value="suggest-size"]').length) return true;

            // Create option
            try {
                let opt = document.createElement('option');
                opt.value = 'suggest-size';
                opt.text = 'Suggest Size';
                opt.className = 'attached enabled';
                if ($sel[0].add) {
                    $sel[0].add(opt, null);
                } else {
                    $sel.append(opt);
                }
            } catch (e) {
                try { $sel.append('<option value="suggest-size" class="attached enabled">Suggest Size</option>'); } catch (ex) { }
            }

            // Refresh Select2/SelectWoo if needed
            try {
                if ($sel.hasClass('select2-hidden-accessible') && $sel.data('select2')) {
                    $sel.trigger('change.select2');
                }
            } catch (e) { }

            return true;
        }

        function ensureAll() {
            $(SELECTORS).each(function () {
                addOptionTo($(this));
            });
        }

        // Store previous value for restoring after Suggest Size click
        $(document).on('focusin mousedown', SELECTORS, function () {
            this.dataset.prevValue = this.value || '';
        });

        // Capture-phase listener to block WC from processing Suggest Size
        document.addEventListener('change', function (evt) {
            let target = evt.target;
            if (!target || target.nodeType !== 1) return;
            if (!(target.matches && $(target).is(SELECTORS))) return;

            if (target.value === 'suggest-size') {
                evt.stopImmediatePropagation();
                evt.stopPropagation();
                evt.preventDefault();

                let prev = target.dataset.prevValue || '';
                if (!prev) {
                    let o = Array.from(target.options).find(o => o.value && o.value !== 'suggest-size');
                    if (o) prev = o.value;
                }
                target.value = prev;

                let $t = $(target);
                if ($t.hasClass('select2-hidden-accessible') && $t.data('select2')) {
                    $t.trigger('change.select2');
                }

                $('#suggest-size-drawer').removeClass('hidden');
            } else {
                $('#suggest-size-drawer').addClass('hidden');
            }
        }, true);

        // Handle Select2/SelectWoo
        $(document).on('select2:selecting', SELECTORS, function (e) {
            let id = e.params?.args?.data?.id || e.params?.data?.id;
            if (id === 'suggest-size') {
                e.preventDefault();
                let prev = this.dataset.prevValue || '';
                $(this).val(prev).trigger('change.select2');
                $('#suggest-size-drawer').removeClass('hidden');
            }
        });

        // Initial run
        ensureAll();

        // Reapply a few times (for lazy WC updates)
        let int = setInterval(function () {
            attempt++;
            ensureAll();
            if (attempt >= maxAttempts || $('option[value="suggest-size"]').length) {
                clearInterval(int);
            }
        }, intervalMs);

        // MutationObserver for DOM rebuilds
        let obsTarget = document.querySelector('form.variations_form') || document.body;
        try {
            let mo = new MutationObserver(function (muts) {
                for (let m of muts) {
                    for (let node of m.addedNodes) {
                        if (node.nodeType === 1) {
                            let $n = $(node);
                            if ($n.is(SELECTORS) || $n.find(SELECTORS).length) {
                                setTimeout(ensureAll, 50);
                            }
                        }
                    }
                }
            });
            mo.observe(obsTarget, { childList: true, subtree: true });
        } catch (e) { }

        // 🔥 WooCommerce variation event hooks — when color or other attributes change
        $(document).on('woocommerce_variation_has_changed woocommerce_update_variation_values', function () {
            setTimeout(ensureAll, 100);
        });

        $('.close-drawer').on('click', function (e) {
            e.preventDefault();

            // re-read cookie and update suggestedSize
            let ssd = getCookie('ssd_data');
            let newSize = null;
            if (ssd) {
                try {
                    let obj = JSON.parse(ssd);
                    if (obj && obj.size) newSize = String(obj.size).toLowerCase();
                } catch (err) { }
            }
            suggestedSize = newSize;

            // remove existing "(suggested)" markers to avoid duplicates and stale markers
            $(SELECTORS).each(function () {
                $(this).find('option').each(function () {
                    let $o = $(this);
                    $o.text(stripSuggestedMarker($o.text()));
                });
            });

            // re-run add/mark logic and refresh Select2/SelectWoo if present
            ensureAll();
            $(SELECTORS).filter('.select2-hidden-accessible').each(function () {
                try { $(this).trigger('change.select2'); } catch (e) { }
            });

            $("#suggest-size-drawer").addClass("hidden");
        });
    });
</script>
<?php endif; }); ?>