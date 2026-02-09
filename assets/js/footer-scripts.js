/**
 * Footer Scripts
 * Handles footer accordion, menu toggle, search form
 * Version: 2.0
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // ============================================================
        // Footer Accordion (Mobile)
        // ============================================================
        var arrowDown = '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M24 24H0V0h24v24z" fill="none" opacity=".87"/><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6-1.41-1.41z"/></svg>';
        var arrowUp = '<svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 0 24 24" width="40px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 8l-6 6 1.41 1.41L12 10.83l4.59 4.58L18 14l-6-6z"/></svg>';

        $(document).on('click', '.footer-title', function() {
            if (window.innerWidth < 1024) {
                $(this).next('.footer-content').slideToggle();
                var $arrow = $(this).find('.arrow');
                var currentHtml = $arrow.html();
                $arrow.html(currentHtml === arrowDown ? arrowUp : arrowDown);
            }
        });

        // Layout on resize / init
        function setLayout() {
            if (window.innerWidth >= 1024) {
                $('.footer-content').show();
                $('.footer-title .arrow').html(arrowDown);
                $('#menu-mobile').addClass('hidden');
            } else {
                $('.footer-content').hide();
                $('#menu-side').addClass('hidden');
            }
        }

        $(window).on('resize', setLayout);
        setLayout();

        // ============================================================
        // Details Arrow Toggle
        // ============================================================
        $(document).on('click', 'details.group > summary', function() {
            var $arrow = $(this).find('.arrow');
            var currentHtml = $arrow.html();
            $arrow.html(currentHtml === arrowDown ? arrowUp : arrowDown);
        });

        // ============================================================
        // Side Menu
        // ============================================================
        $('#menu-side-close-btn').on('click', function(e) {
            e.preventDefault();
            $('#menu-side').addClass('hidden');
        });

        $('#menu-side').on('click', function(e) {
            if ($(e.target).closest('#menu-side-close-btn').length) return;
            e.stopPropagation();
        });

        // ============================================================
        // Search Form
        // ============================================================
        $(document).on('click', '.search-icon-header', function(e) {
            e.stopPropagation();
            $('#searchform').removeClass('hidden');
            $('#s').focus();
        });

        $('#searchform').on('click', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', function() {
            $('#searchform, #menu-side').addClass('hidden');
        });

        // ============================================================
        // Mobile Menu (open / close)
        // ============================================================
        $('#menu-mobile-btn').on('click', function() {
            $('#menu-mobile').toggleClass('hidden');
            initDeepMobileSubmenus();
        });

        $('#menu-mobile-close-btn').on('click', function() {
            $('#menu-mobile').addClass('hidden');
        });

        // ============================================================
        // Mobile deep submenus (level > 2)
        // ============================================================
        function initDeepMobileSubmenus() {
            var isMobile = window.innerWidth < 1024;
            var $menu = $('#menu-mobile');
            if (!$menu.length) return;

            // On mobile, collapse all submenus deeper than level 1 (i.e. any li.menu-item-has-children).
            // On desktop, leave everything visible.
            var $deepParents = $menu.find('li.menu-item-has-children');

            $deepParents.each(function() {
                var $li = $(this);
                var $submenu = $li.children('.sub-menu');
                if (!$submenu.length) return;

                if (!isMobile) {
                    // Desktop: ensure everything is visible; do not collapse or modify structure.
                    $submenu.show();
                    return;
                }

                // Mobile behavior for level > 1
                if ($li.data('deepMobileInit')) {
                    // Ensure collapsed by default every time we re-enter mobile.
                    $submenu.hide();
                    return;
                }

                $li.data('deepMobileInit', true);

                var $link = $li.children('a').first();
                var $toggle = $('<button type="button" class="mobile-deep-submenu-toggle" aria-expanded="false" aria-label="Toggle submenu"><span class="mobile-deep-submenu-toggle-icon">+</span></button>');

                if ($link.length) {
                    $link.after($toggle);
                } else {
                    $li.prepend($toggle);
                }

                // Collapse deep submenu by default on mobile.
                $submenu.hide();
            });
        }

        // Initialize deep submenu state on load + on resize.
        $(window).on('resize', initDeepMobileSubmenus);
        initDeepMobileSubmenus();

        // Toggle deep submenu open/close on mobile
        $(document).on('click', '.mobile-deep-submenu-toggle', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $li = $btn.closest('li');
            var $submenu = $li.children('.sub-menu');
            if (!$submenu.length) return;

            $submenu.stop(true, true).slideToggle(200);
            var expanded = $btn.attr('aria-expanded') === 'true';
            $btn.attr('aria-expanded', expanded ? 'false' : 'true');
            $btn.find('.mobile-deep-submenu-toggle-icon').text(expanded ? '+' : '−');
        });

        // ============================================================
        // Header cart: open right sidebar popup (same as .xoo-wsc-basket)
        // ============================================================
        $(document).on('click', '.aesir-open-side-cart', function(e) {
            var $sideCartTrigger = $('.xoo-wsc-basket').first();
            if ($sideCartTrigger.length) {
                e.preventDefault();
                $sideCartTrigger[0].click();
            }
        });
    });

})(jQuery);
