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
 * Default language codes for header when GTranslate list cannot be read from settings.
 *
 * @return array<int, string>
 */
function aesir_gtranslate_default_lang_codes() {
	return array( 'en', 'ar', 'zh-CN', 'nl', 'fr', 'de', 'it', 'ja', 'ko', 'pt', 'ru', 'es', 'th', 'vi' );
}

/**
 * Language code to flag emoji (for GTranslate-style UI).
 *
 * @return array<string, string>
 */
function aesir_language_flag_emojis() {
	return array(
		'en' => '🇬🇧', 'ar' => '🇸🇦', 'zh-CN' => '🇨🇳', 'zh-TW' => '🇹🇼', 'nl' => '🇳🇱', 'fr' => '🇫🇷',
		'de' => '🇩🇪', 'it' => '🇮🇹', 'ja' => '🇯🇵', 'ko' => '🇰🇷', 'pt' => '🇵🇹', 'ru' => '🇷🇺',
		'es' => '🇪🇸', 'th' => '🇹🇭', 'vi' => '🇻🇳', 'af' => '🇿🇦', 'sq' => '🇦🇱', 'hy' => '🇦🇲',
		'az' => '🇦🇿', 'eu' => '🇪🇸', 'be' => '🇧🇾', 'bg' => '🇧🇬', 'ca' => '🇪🇸', 'hr' => '🇭🇷',
		'cs' => '🇨🇿', 'da' => '🇩🇰', 'et' => '🇪🇪', 'tl' => '🇵🇭', 'fi' => '🇫🇮', 'gl' => '🇪🇸',
		'el' => '🇬🇷', 'ht' => '🇭🇹', 'iw' => '🇮🇱', 'hi' => '🇮🇳', 'hu' => '🇭🇺', 'is' => '🇮🇸',
		'id' => '🇮🇩', 'ga' => '🇮🇪', 'lv' => '🇱🇻', 'lt' => '🇱🇹', 'mk' => '🇲🇰', 'ms' => '🇲🇾',
		'mt' => '🇲🇹', 'no' => '🇳🇴', 'fa' => '🇮🇷', 'pl' => '🇵🇱', 'ro' => '🇷🇴', 'sr' => '🇷🇸',
		'sk' => '🇸🇰', 'sl' => '🇸🇮', 'sw' => '🇹🇿', 'sv' => '🇸🇪', 'tr' => '🇹🇷', 'uk' => '🇺🇦',
		'ur' => '🇵🇰', 'cy' => '🇬🇧', 'yi' => '🇮🇱', 'ka' => '🇬🇪',
	);
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
	// GTranslate may be active but option not set (e.g. gtranslate.io widget, or plugin just activated)
	$active = get_option( 'active_plugins', array() );
	if ( is_array( $active ) ) {
		foreach ( $active as $plugin ) {
			if ( stripos( $plugin, 'gtranslate' ) !== false ) {
				return 'gtranslate';
			}
		}
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

	// GTranslate: from plugin settings (incl_langs) or by parsing widget_code; fallback to default when option missing
	$provider_pre = aesir_get_language_switcher_provider();
	$gt_data      = get_option( 'GTranslate' );
	if ( $provider_pre === 'gtranslate' && ( ! is_array( $gt_data ) || empty( $gt_data['widget_code'] ) ) ) {
		// Plugin active but no option (e.g. gtranslate.io widget): use full default list so header matches bottom
		$names   = aesir_gtranslate_language_names();
		$default = 'en';
		$codes   = aesir_gtranslate_default_lang_codes();
		foreach ( $codes as $code ) {
			$languages[] = array(
				'url'    => $default . '|' . $code,
				'name'   => isset( $names[ $code ] ) ? $names[ $code ] : $code,
				'code'   => $code,
				'active' => false,
			);
		}
		return $languages;
	}
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
			// Normalize: collapse newlines and extra space inside tags so regex can match
			$code_norm = preg_replace( '/\s+/', ' ', $code );
			// Match double- or single-quoted value with a pipe (lang pair); allow any chars in option text
			if ( preg_match_all( '#<option\s+value=(["\'])([^"\']*\|[^"\']+)\1[^>]*>\s*([^<]*)\s*</option>#', $code_norm, $m, PREG_SET_ORDER ) ) {
				foreach ( $m as $match ) {
					if ( isset( $match[2] ) && $match[2] !== '' ) {
						$pairs[] = array(
							'value' => $match[2],
							'name'  => trim( wp_strip_all_tags( $match[3] ) ),
						);
					}
				}
			}
			// Also try unnormalized (options may be split across lines)
			if ( empty( $pairs ) && preg_match_all( '#<option\s+value=(["\'])([^"\']*\|[^"\']+)\1[^>]*>([^<]*(?:<[^>]+>[^<]*)*)</option>#s', $code, $m, PREG_SET_ORDER ) ) {
				foreach ( $m as $match ) {
					if ( isset( $match[2] ) && $match[2] !== '' ) {
						$pairs[] = array(
							'value' => $match[2],
							'name'  => trim( wp_strip_all_tags( $match[3] ) ),
						);
					}
				}
			}
			// Very permissive: any option with value containing |
			if ( empty( $pairs ) && preg_match_all( '#<option[^>]+value=(["\'])([^"\']*\|[^"\']+)\1[^>]*>([^<]+)#', $code_norm, $m, PREG_SET_ORDER ) ) {
				foreach ( $m as $match ) {
					if ( isset( $match[2] ) && $match[2] !== '' ) {
						$pairs[] = array(
							'value' => $match[2],
							'name'  => trim( wp_strip_all_tags( $match[3] ) ),
						);
					}
				}
			}
			// If we got fewer than 5 from parsing, use full default list so header matches GTranslate
			if ( empty( $pairs ) || count( $pairs ) < 5 ) {
				$codes = aesir_gtranslate_default_lang_codes();
				$pairs = array();
				foreach ( $codes as $code ) {
					$pairs[] = array(
						'value' => $default . '|' . $code,
						'name'  => isset( $names[ $code ] ) ? $names[ $code ] : $code,
					);
				}
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
 * Renders when WPML/Polylang have 2+ languages, or GTranslate is active (show with 1+ languages).
 * Selection is persisted by the active plugin (cookies / URL).
 */
function aesir_language_switcher() {
	$languages = aesir_get_available_languages();
	$provider  = aesir_get_language_switcher_provider();
	$count     = count( $languages );

	$show = ( $count >= 2 ) || ( $provider === 'gtranslate' && $count >= 1 );
	if ( ! $show ) {
		return;
	}

	$current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$id          = 'aesir-language-switcher';
	$is_gt       = ( $provider === 'gtranslate' );
	$default_gt  = $is_gt && ( $gt = get_option( 'GTranslate' ) ) && is_array( $gt ) && isset( $gt['default_language'] ) ? $gt['default_language'] : 'en';
	$populate_from_dom = $is_gt && $count < 5;
	$flags       = $is_gt ? aesir_language_flag_emojis() : array();

	if ( $is_gt ) :
		// GTranslate: custom dropdown with flags (matches bottom widget UI)
		?>
		<div class="aesir-language-switcher-wrap aesir-ls" id="aesir-ls-wrap" data-provider="gtranslate" data-default-lang="<?php echo esc_attr( $default_gt ); ?>" <?php echo $populate_from_dom ? ' data-populate-from-dom="1"' : ''; ?>>
			<button type="button" class="aesir-ls__current" id="aesir-ls-current" aria-haspopup="listbox" aria-expanded="false" aria-label="<?php esc_attr_e( 'Select language', 'aesir' ); ?>">
				<span class="aesir-ls__flag" id="aesir-ls-flag" aria-hidden="true"><?php echo isset( $flags[ $default_gt ] ) ? esc_html( $flags[ $default_gt ] ) : '🌐'; ?></span>
				<span class="aesir-ls__label" id="aesir-ls-label"><?php echo esc_html( isset( $languages[0] ) ? $languages[0]['name'] : 'English' ); ?></span>
				<span class="aesir-ls__chevron" aria-hidden="true">▼</span>
			</button>
			<ul class="aesir-ls__list" id="aesir-ls-list" role="listbox" hidden>
				<?php foreach ( $languages as $lang ) :
					$flag = isset( $flags[ $lang['code'] ] ) ? $flags[ $lang['code'] ] : '🌐';
				?>
					<li class="aesir-ls__option" role="option" tabindex="-1" data-value="<?php echo esc_attr( $lang['url'] ); ?>" data-code="<?php echo esc_attr( $lang['code'] ); ?>">
						<span class="aesir-ls__option-flag" aria-hidden="true"><?php echo esc_html( $flag ); ?></span>
						<span class="aesir-ls__option-label"><?php echo esc_html( $lang['name'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<select id="<?php echo esc_attr( $id ); ?>" class="aesir-ls__native" aria-hidden="true" tabindex="-1" data-current-url="<?php echo esc_attr( $current_url ); ?>">
				<?php foreach ( $languages as $lang ) : ?>
					<option value="<?php echo esc_attr( $lang['url'] ); ?>"><?php echo esc_html( $lang['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	else :
		// WPML / Polylang: native select
		?>
		<div class="aesir-language-switcher-wrap inline-flex items-center">
			<label for="<?php echo esc_attr( $id ); ?>" class="sr-only"><?php esc_html_e( 'Language', 'aesir' ); ?></label>
			<select id="<?php echo esc_attr( $id ); ?>"
				class="aesir-language-switcher border border-black bg-white text-black text-sm py-1 pl-2 pr-6 max-w-[140px] cursor-pointer appearance-none focus:outline-none focus:ring-1 focus:ring-black"
				aria-label="<?php esc_attr_e( 'Select language', 'aesir' ); ?>"
				data-current-url="<?php echo esc_attr( $current_url ); ?>">
				<?php foreach ( $languages as $lang ) : ?>
					<option value="<?php echo esc_url( $lang['url'] ); ?>" <?php selected( ! empty( $lang['active'] ) ); ?>><?php echo esc_html( $lang['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	endif;
}

/**
 * Enqueue inline script for language switcher (redirect or GTranslate cookie + reload).
 */
function aesir_language_switcher_script() {
	$languages = aesir_get_available_languages();
	$provider  = aesir_get_language_switcher_provider();
	$count     = count( $languages );
	$run       = ( $count >= 2 ) || ( $provider === 'gtranslate' && $count >= 1 );
	if ( ! $run ) {
		return;
	}
	$is_gt = ( $provider === 'gtranslate' );
	?>
	<script>
	(function() {
		function gtGetCookie(name) {
			var c = document.cookie.split(';');
			for (var i = 0; i < c.length; i++) {
				var p = c[i].trim().split('=');
				if (p[0] === name) return p[1] ? decodeURIComponent(p[1]) : '';
			}
			return '';
		}
		function gtSetCookie(from, to) {
			if (to === from || !to) {
				document.cookie = 'googtrans=; path=/; max-age=0';
			} else {
				document.cookie = 'googtrans=/' + from + '/' + to + '; path=/; max-age=31536000; SameSite=Lax';
			}
		}
		function gtApplyLanguage(langPair) {
			if (!langPair) return;
			var parts = langPair.split('|');
			var from = parts[0] || 'en';
			var to = parts[1] || from;
			gtSetCookie(from, to);
			if (typeof doGTranslate === 'function') {
				try { doGTranslate(langPair); } catch (e) {}
			}
			location.reload();
		}

		var wrap = document.getElementById('aesir-ls-wrap');
		if (wrap && wrap.getAttribute('data-provider') === 'gtranslate') {
			var currentBtn = document.getElementById('aesir-ls-current');
			var list = document.getElementById('aesir-ls-list');
			var flagEl = document.getElementById('aesir-ls-flag');
			var labelEl = document.getElementById('aesir-ls-label');
			var sel = document.getElementById('aesir-language-switcher');
			var defaultLang = wrap.getAttribute('data-default-lang') || 'en';
			var googtrans = gtGetCookie('googtrans');
			var currentCode = defaultLang;
			if (googtrans) {
				var segs = googtrans.split('/').filter(Boolean);
				if (segs.length >= 2) currentCode = segs[1];
			}
			var pair = defaultLang + '|' + currentCode;
			var options = list ? list.querySelectorAll('.aesir-ls__option') : [];
			for (var i = 0; i < options.length; i++) {
				if (options[i].getAttribute('data-value') === pair) {
					if (flagEl) flagEl.textContent = options[i].querySelector('.aesir-ls__option-flag').textContent;
					if (labelEl) labelEl.textContent = options[i].querySelector('.aesir-ls__option-label').textContent;
					if (sel) { sel.value = pair; sel.selectedIndex = i; }
					break;
				}
			}
			currentBtn.addEventListener('click', function(e) {
				e.stopPropagation();
				var open = list.getAttribute('hidden') === null;
				if (open) { list.setAttribute('hidden', ''); currentBtn.setAttribute('aria-expanded', 'false'); }
				else { list.removeAttribute('hidden'); currentBtn.setAttribute('aria-expanded', 'true'); }
			});
			document.addEventListener('click', function() {
				list.setAttribute('hidden', '');
				currentBtn.setAttribute('aria-expanded', 'false');
			});
			list.addEventListener('click', function(e) {
				e.stopPropagation();
				var opt = e.target.closest('.aesir-ls__option');
				if (!opt) return;
				var val = opt.getAttribute('data-value');
				if (!val) return;
				list.setAttribute('hidden', '');
				currentBtn.setAttribute('aria-expanded', 'false');
				gtApplyLanguage(val);
			});
			return;
		}

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
