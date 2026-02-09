<?php
/**
 * Language Switcher (Header)
 * Compatible with WPML, Polylang, and GTranslate. Persists selection via plugin mechanisms or cookie.
 *
 * @package Aesir_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GTranslate language code to name map (matches plugin dropdown languages).
 *
 * @return array<string, string>
 */
function aesir_gtranslate_language_names() {
	return array(
		'af' => 'Afrikaans', 'sq' => 'Albanian', 'ar' => 'Arabic', 'hy' => 'Armenian',
		'az' => 'Azerbaijani', 'eu' => 'Basque', 'be' => 'Belarusian', 'bg' => 'Bulgarian',
		'ca' => 'Catalan', 'zh-CN' => 'Chinese (Simplified)', 'zh-TW' => 'Chinese (Traditional)',
		'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'nl' => 'Dutch',
		'en' => 'English', 'et' => 'Estonian', 'tl' => 'Filipino', 'fi' => 'Finnish',
		'fr' => 'French', 'gl' => 'Galician', 'ka' => 'Georgian', 'de' => 'German',
		'el' => 'Greek', 'ht' => 'Haitian Creole', 'iw' => 'Hebrew', 'hi' => 'Hindi',
		'hu' => 'Hungarian', 'is' => 'Icelandic', 'id' => 'Indonesian', 'ga' => 'Irish',
		'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'lv' => 'Latvian',
		'lt' => 'Lithuanian', 'mk' => 'Macedonian', 'ms' => 'Malay', 'mt' => 'Maltese',
		'no' => 'Norwegian', 'fa' => 'Persian', 'pl' => 'Polish', 'pt' => 'Portuguese',
		'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian', 'sk' => 'Slovak',
		'sl' => 'Slovenian', 'es' => 'Spanish', 'sw' => 'Swahili', 'sv' => 'Swedish',
		'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu',
		'vi' => 'Vietnamese', 'cy' => 'Welsh', 'yi' => 'Yiddish',
	);
}

/**
 * Returns which language provider is active: 'wpml', 'polylang', 'gtranslate', or ''.
 *
 * @return string
 */
function aesir_get_language_switcher_provider() {
	if ( function_exists( 'icl_get_languages' ) ) {
		return 'wpml';
	}
	if ( function_exists( 'pll_the_languages' ) ) {
		return 'polylang';
	}
	if ( class_exists( 'GTranslate', false ) || get_option( 'GTranslate' ) ) {
		return 'gtranslate';
	}
	return '';
}

/**
 * Returns list of languages for the switcher.
 * - WPML: uses icl_get_languages()
 * - Polylang: uses pll_the_languages() return array
 * - GTranslate: uses get_option('GTranslate') incl_langs + default_language
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

	// GTranslate: from plugin settings (incl_langs) or by parsing widget_code
	$gt_data = get_option( 'GTranslate' );
	if ( is_array( $gt_data ) && ! empty( $gt_data['widget_code'] ) ) {
		$default = isset( $gt_data['default_language'] ) ? $gt_data['default_language'] : 'en';
		$names   = aesir_gtranslate_language_names();
		$pairs   = array(); // list of [ 'value' => 'en|es', 'name' => 'Spanish' ]

		// Prefer incl_langs from options (same list as in Settings)
		$incl = isset( $gt_data['incl_langs'] ) ? $gt_data['incl_langs'] : array();
		if ( is_string( $incl ) ) {
			$incl = array_filter( array_map( 'trim', explode( ',', $incl ) ) );
		} elseif ( ! is_array( $incl ) ) {
			$incl = array();
		}
		if ( ! empty( $incl ) ) {
			$codes = array_unique( array_merge( array( $default ), $incl ) );
			foreach ( $codes as $code ) {
				$pairs[] = array(
					'value' => $default . '|' . $code,
					'name'  => isset( $names[ $code ] ) ? $names[ $code ] : $code,
				);
			}
		} else {
			// Fallback: parse widget_code for <option value="default|code">Name</option> (same as bottom widget)
			$code = $gt_data['widget_code'];
			// Match double- or single-quoted value with a pipe (lang pair)
			if ( preg_match_all( '#<option\s+value=(["\'])([^"\']*\|[^"\']+)\1[^>]*>([^<]+)</option>#', $code, $m, PREG_SET_ORDER ) ) {
				foreach ( $m as $match ) {
					if ( isset( $match[2] ) && $match[2] !== '' ) {
						$pairs[] = array(
							'value' => $match[2],
							'name'  => trim( $match[3] ),
						);
					}
				}
			}
			// If no options with lang pair found, ensure default is available
			if ( empty( $pairs ) ) {
				$pairs[] = array(
					'value' => $default . '|' . $default,
					'name'  => isset( $names[ $default ] ) ? $names[ $default ] : $default,
				);
			}
		}

		foreach ( $pairs as $p ) {
			$languages[] = array(
				'url'    => $p['value'],
				'name'   => $p['name'],
				'code'   => substr( strrchr( $p['value'], '|' ), 1 ),
				'active' => false,
			);
		}
		return $languages;
	}

	return $languages;
}

/**
 * Outputs the language switcher dropdown markup.
 * Renders when WPML, Polylang, or GTranslate is active and more than one language exists.
 * Selection is persisted by the active plugin (cookies / URL).
 */
function aesir_language_switcher() {
	$languages = aesir_get_available_languages();
	$provider  = aesir_get_language_switcher_provider();

	if ( count( $languages ) < 2 ) {
		return;
	}

	$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$id          = 'aesir-language-switcher';
	$is_gt       = ( $provider === 'gtranslate' );
	$default_gt  = $is_gt && ( $gt = get_option( 'GTranslate' ) ) && is_array( $gt ) && isset( $gt['default_language'] ) ? $gt['default_language'] : 'en';
	?>
	<div class="aesir-language-switcher-wrap inline-flex items-center">
		<label for="<?php echo esc_attr( $id ); ?>" class="sr-only"><?php esc_html_e( 'Language', 'aesir' ); ?></label>
		<select id="<?php echo esc_attr( $id ); ?>"
			class="aesir-language-switcher border border-black bg-white text-black text-sm py-1 pl-2 pr-6 max-w-[120px] cursor-pointer appearance-none focus:outline-none focus:ring-1 focus:ring-black"
			aria-label="<?php esc_attr_e( 'Select language', 'aesir' ); ?>"
			data-current-url="<?php echo esc_attr( $current_url ); ?>"
			<?php if ( $is_gt ) : ?>
				data-provider="gtranslate"
				data-default-lang="<?php echo esc_attr( $default_gt ); ?>"
			<?php endif; ?>>
			<?php foreach ( $languages as $lang ) : ?>
				<option value="<?php echo esc_attr( $lang['url'] ); ?>" <?php selected( ! empty( $lang['active'] ) ); ?>>
					<?php echo esc_html( $lang['name'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

/**
 * Enqueue inline script for language switcher (redirect or GTranslate cookie/doGTranslate).
 */
function aesir_language_switcher_script() {
	if ( count( aesir_get_available_languages() ) < 2 ) {
		return;
	}
	$provider = aesir_get_language_switcher_provider();
	?>
	<script>
	(function() {
		var sel = document.getElementById('aesir-language-switcher');
		if (!sel) return;
		var provider = sel.getAttribute('data-provider');

		function gtGetCookie(name) {
			var c = document.cookie.split(';');
			for (var i = 0; i < c.length; i++) {
				var p = c[i].trim().split('=');
				if (p[0] === name) return p[1] ? decodeURIComponent(p[1]) : '';
			}
			return '';
		}

		if (provider === 'gtranslate') {
			var defaultLang = sel.getAttribute('data-default-lang') || 'en';
			var googtrans = gtGetCookie('googtrans');
			if (googtrans) {
				var parts = googtrans.split('/').filter(Boolean);
				var current = parts.length >= 2 ? parts[1] : defaultLang;
				var pair = defaultLang + '|' + current;
				for (var j = 0; j < sel.options.length; j++) {
					if (sel.options[j].value === pair) { sel.selectedIndex = j; break; }
				}
			}
			sel.addEventListener('change', function() {
				var langPair = this.value;
				if (!langPair) return;
				if (typeof doGTranslate === 'function') {
					doGTranslate(langPair);
					return;
				}
				var parts = langPair.split('|');
				var to = parts[1] || defaultLang;
				if (to === defaultLang) {
					document.cookie = 'googtrans=; path=/; max-age=0';
				} else {
					document.cookie = 'googtrans=/' + defaultLang + '/' + to + '; path=/; max-age=31536000';
				}
				location.reload();
			});
		} else {
			sel.addEventListener('change', function() {
				var url = this.value;
				if (url) window.location.href = url;
			});
		}
	})();
	</script>
	<?php
}

add_action( 'wp_footer', 'aesir_language_switcher_script', 5 );
