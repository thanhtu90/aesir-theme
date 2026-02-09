<?php
/**
 * Child Menu Functions
 * Version: 2.0 - Performance Optimized
 *
 * FIXES APPLIED:
 * - PERF-003: Pre-load menu data instead of AJAX per hover
 * - Added caching for menu structure
 */

defined('ABSPATH') || exit;

/**
 * Get all menu items with their children structure
 * Used to pre-load menu data on page load
 */
function aesir_get_menu_tree() {
    // Cache key
    $cache_key = 'aesir_menu_tree';

    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $locations = get_nav_menu_locations();
    if (!isset($locations['main-menu'])) {
        return [];
    }

    $menu_id = $locations['main-menu'];
    $menu_items = wp_get_nav_menu_items($menu_id);

    if (!$menu_items) {
        return [];
    }

    // Build parent → children map
    $tree = [];
    $items_by_id = [];

    foreach ($menu_items as $item) {
        $items_by_id[$item->ID] = $item;
        $pid = intval($item->menu_item_parent);

        if (!isset($tree[$pid])) {
            $tree[$pid] = [];
        }
        $tree[$pid][] = $item;
    }

    // Build result with all parent IDs and their descendants
    $result = [];

    foreach ($menu_items as $item) {
        if (intval($item->menu_item_parent) === 0) {
            // Top-level item
            $children = aesir_collect_descendants($item->ID, $tree);
            if (!empty($children)) {
                $result['menu-item-' . $item->ID] = $children;
            }
        }
    }

    // Cache for 1 hour (cleared on menu save)
    set_transient($cache_key, $result, HOUR_IN_SECONDS);

    return $result;
}

/**
 * Recursively collect all descendants of a menu item
 */
function aesir_collect_descendants($parent_id, &$tree, $depth = 1) {
    $result = [];

    if (!isset($tree[$parent_id])) {
        return $result;
    }

    foreach ($tree[$parent_id] as $child) {
        $result[] = [
            'ID' => $child->ID,
            'title' => $child->title,
            'url' => $child->url,
            'depth' => $depth,
        ];

        // Recursively get children
        $grandchildren = aesir_collect_descendants($child->ID, $tree, $depth + 1);
        $result = array_merge($result, $grandchildren);
    }

    return $result;
}

/**
 * Clear menu cache when menu is updated
 */
add_action('wp_update_nav_menu', function() {
    delete_transient('aesir_menu_tree');
});

/**
 * Output pre-loaded menu data as JSON in footer
 * FIX PERF-003: Load all menu data once, no AJAX per hover
 */
add_action('wp_footer', function() {
    $menu_data = aesir_get_menu_tree();
    ?>
    <script type="text/javascript">
        var aesirMenuData = <?php echo json_encode($menu_data); ?>;

        jQuery(function($) {
            function renderChildMenu(children) {
                var $slide = $('#child-menu-slide');

                if (children && children.length) {
                    var $ul = $('<ul>');

                    children.forEach(function(item) {
                        var decodedTitle = $('<div/>').html(item.title).text();
                        var padding = (item.depth * 15) + 'px';

                        var $li = $('<li>').css('padding-left', padding);
                        var $a = $('<a>')
                            .attr('href', item.url)
                            .text(decodedTitle);

                        $li.append($a);
                        $ul.append($li);
                    });

                    $slide.removeClass('hidden').empty().append($ul);
                    $('#menu-side').removeClass('hidden');
                } else {
                    $slide.addClass('hidden').empty();
                    $('#menu-side').addClass('hidden');
                }
            }

            // Use pre-loaded data instead of AJAX
            $('.header-menu').on('mouseenter', 'li', function() {
                var id = $(this).attr('id');
                if (!id) return;

                // Look up in pre-loaded data - no AJAX needed!
                if (aesirMenuData && aesirMenuData[id]) {
                    renderChildMenu(aesirMenuData[id]);
                } else {
                    $('#child-menu-slide').addClass('hidden').empty();
                }
            });
        });
    </script>
    <?php
});

/**
 * AJAX handler for backward compatibility
 * Kept for any external systems that might use it
 */
add_action('wp_ajax_get_child_menu_items', 'ajax_get_child_menu_items');
add_action('wp_ajax_nopriv_get_child_menu_items', 'ajax_get_child_menu_items');

function ajax_get_child_menu_items() {
    // Rate limiting
    if (!aesir_check_rate_limit('menu_items', 60, 60)) {
        wp_send_json_error(['message' => 'Rate limit exceeded']);
        wp_die();
    }

    if (!isset($_POST['parent_id'])) {
        wp_send_json_error(['message' => 'Missing parent_id']);
        wp_die();
    }

    $parent_id = 0;
    if (preg_match('/(\d+)$/', $_POST['parent_id'], $matches)) {
        $parent_id = intval($matches[1]);
    }

    if ($parent_id === 0) {
        wp_send_json_success([]);
        wp_die();
    }

    $menu_data = aesir_get_menu_tree();
    $key = 'menu-item-' . $parent_id;

    if (isset($menu_data[$key])) {
        wp_send_json_success($menu_data[$key]);
    } else {
        wp_send_json_success([]);
    }

    wp_die();
}
