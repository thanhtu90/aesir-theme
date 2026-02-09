<?php
/**
 * Language Switcher (Header)
 * Compatible with WPML and Polylang. Persists selection via plugin mechanisms or cookie fallback.
 *
 * @package Aesir_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns list of languages for the switcher.
 * - WPML: uses icl_get_languages()
 * - Polylang: uses pll_the_languages() return array
 * - Fallback: empty array (switcher not rendered when no plugin)
 *
 * @return array<int, array{url: string, name: string, code: string, active: bool}>
 */
function aesir_get_available_languages() {
	$languages = array();

	// WPML
	if ( function_exists( 'icl_get_languages' ) ) {
		$wpml_langs = icl_get_languages( array( 'skip_missing' => 0 ) );
		if ( ! empty( $wpml_langs ) && is_array( $wpml_langs ) ) {
			foreach ( $wpml_langs as $lang ) {
				$languages[] = array(
					'url'    => isset( $lang['url'] ) ? $lang['url'] : '',
					'name'   => isset( $lang['native_name'] ) ? $lang['native_name'] : ( isset( $lang['translated_name'] ) ? $lang['translated_name'] : $lang['code'] ),
					'code'   => isset( $lang['code'] ) ? $lang['code'] : '',
					'active' => ! empty( $lang['active'] ),
				);
			}
		}
		return $languages;
	}

	// Polylang (returns array when not echoing)
	if ( function_exists( 'pll_the_languages' ) ) {
		$pll_langs = pll_the_languages( array( 'raw' => 1 ) );
		if ( ! empty( $pll_langs ) && is_array( $pll_langs ) ) {
			foreach ( $pll_langs as $lang ) {
				$languages[] = array(
					'url'    => isset( $lang['url'] ) ? $lang['url'] : '',
					'name'   => isset( $lang['name'] ) ? $lang['name'] : ( isset( $lang['slug'] ) ? $lang['slug'] : '' ),
					'code'   => isset( $lang['slug'] ) ? $lang['slug'] : '',
					'active' => ! empty( $lang['current_lang'] ),
				);
			}
		}
		return $languages;
	}

	return $languages;
}

/**
 * Outputs the language switcher dropdown markup.
 * Only renders when WPML or Polylang is active and more than one language exists.
 * Selection is persisted by the active plugin (WPML/Polylang use cookies and URL).
 */
function aesir_language_switcher() {
	$languages = aesir_get_available_languages();

	if ( count( $languages ) < 2 ) {
		return;
	}

	$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$id          = 'aesir-language-switcher';
	?>
	<div class="aesir-language-switcher-wrap inline-flex items-center">
		<label for="<?php echo esc_attr( $id ); ?>" class="sr-only"><?php esc_html_e( 'Language', 'aesir' ); ?></label>
		<select id="<?php echo esc_attr( $id ); ?>"
			class="aesir-language-switcher border border-black bg-white text-black text-sm py-1 pl-2 pr-6 max-w-[120px] cursor-pointer appearance-none focus:outline-none focus:ring-1 focus:ring-black"
			aria-label="<?php esc_attr_e( 'Select language', 'aesir' ); ?>"
			data-current-url="<?php echo esc_attr( $current_url ); ?>">
			<?php foreach ( $languages as $lang ) : ?>
				<option value="<?php echo esc_url( $lang['url'] ); ?>" <?php selected( ! empty( $lang['active'] ) ); ?>>
					<?php echo esc_html( $lang['name'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

/**
 * Enqueue inline script for language switcher redirect (only when switcher is used).
 */
function aesir_language_switcher_script() {
	if ( count( aesir_get_available_languages() ) < 2 ) {
		return;
	}
	?>
	<script>
	(function() {
		var sel = document.getElementById('aesir-language-switcher');
		if (!sel) return;
		sel.addEventListener('change', function() {
			var url = this.value;
			if (url) window.location.href = url;
		});
	})();
	</script>
	<?php
}

add_action( 'wp_footer', 'aesir_language_switcher_script', 5 );
