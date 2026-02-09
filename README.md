# Aesir Theme

A WordPress theme for **Aesir Studio**, built for WooCommerce with Pancake inventory integration, custom checkout flows, and a minimal product-focused design.

**Author:** Tan Nguyen  
**Version:** 1

---

## Overview

Aesir Theme is a standalone WordPress theme that provides:

- **WooCommerce** storefront with custom product grids, single product layout, cart/checkout styling, and account pages
- **Pancake** integration for real-time stock by SKU, order sync, webhooks, and loyalty display
- **Checkout customizations**: thank-you card fields, payment method handling, state/locale overrides
- **Front-end**: Tailwind CSS, Swiper sliders (video + product), responsive layout, size-suggestion drawer

The codebase is structured for security and performance (v2.0): API keys in `wp-config.php`, conditional script/style loading, rate limiting, and debug-only logging.

---

## Requirements

- WordPress (with permalinks enabled)
- WooCommerce
- PHP 7.4+
- Node.js & npm (for building CSS)

**Optional / integration:**

- **Pancake** (inventory/orders): requires API key, shop ID, warehouse ID, and webhook secret in `wp-config.php` (see [Configuration](#configuration))
- **PayPal** (WooCommerce PayPal Payments): supported via checkout customizations

---

## Installation

1. Clone or copy the theme into `wp-content/themes/aesir-theme` (or `wp-content/themes/aesir` if your paths use that).
2. In WordPress: **Appearance → Themes**, activate **Aesir Theme**.
3. Install and activate **WooCommerce** if not already installed.
4. Build assets (see [Development](#development)).

---

## Configuration

### Pancake (optional)

Add these constants to `wp-config.php` (above “That’s all, stop editing!”):

```php
define('PANCAKE_API_KEY', 'your-api-key');
define('PANCAKE_SHOP_ID', 'your-shop-id');
define('PANCAKE_WAREHOUSE_ID', 'your-warehouse-id');
define('PANCAKE_WEBHOOK_SECRET', 'your-webhook-secret');
```

Used for: live stock by SKU on product pages, order sync to Pancake, webhook for order updates, and loyalty info on the My Account page.

### Theme options

If **Theme Options** is used (e.g. via a separate plugin or manual include of `functions/themes-option.php`), it provides:

- Header logo and logo text  
- Footer: copyright, address, Facebook, Instagram, Pinterest, YouTube  

The theme’s main `functions.php` does not include `themes-option.php` or `paypal-base-currency.php` by default; add them via a child theme or mu-plugin if needed.

---

## Development

### CSS (Tailwind)

- **Entry:** `assets/css/style.scss` (imports Tailwind and project partials).
- **Output:** `assets/css/output.scss` → process to `output.css` (e.g. with PostCSS/Sass).

Build (from theme root):

```bash
npm install
npm run build
```

`package.json` uses Tailwind to compile `./assets/css/style.scss` → `./assets/css/output.scss`; add a step to compile SCSS to CSS and/or copy to `output.css` if your stack expects `output.css`.

### Tailwind config

- **Config:** `tailwind.config.js`  
- **Content:** `./themes/aesir/**/*.php`, `./*.php`, `./template-parts/**/*.php`, `./assets/**/*.js`  
- **Theme:** Custom breakpoints (sm/md/lg/xl/2xl), Montserrat font, `slide-in` / `slide-out` animations.

Adjust the `content` paths if the theme lives in a different directory (e.g. `aesir-theme` instead of `aesir`).

### JS

- **Footer (global):** `assets/js/footer-scripts.js` (menu, footer behavior).
- **Product/shop:** `assets/js/product-page.js` (enqueued on product/shop/category).
- **Checkout:** `assets/js/checkout-methods.js` (enqueued on checkout).
- **Infinite scroll:** `assets/js/infinite-scroll.js` (enqueued where needed).

Swiper is loaded only on front page, shop, product, and product category.

---

## Project structure

| Path | Purpose |
|------|--------|
| `style.css` | Theme metadata + WooCommerce/product grid and UI overrides |
| `functions.php` | Setup, enqueue, helpers, and inclusion of function modules |
| `functions/` | Feature modules (see below) |
| `template-parts/` | Video slider, bestsellers, featured products, nav, product slider, size-suggestion drawer |
| `woocommerce/` | Overrides for archive, single product, content-product, variable add-to-cart |
| `assets/css/` | Tailwind entry (`style.scss`), partials, Swiper CSS, output |
| `assets/js/` | Theme and checkout scripts, Swiper |
| `assets/images/` | Logos, placeholders, size-guide (chest/stomach), video posters |
| `page-home.php` | Home template (video slider + bestsellers) |

### Function modules (included from `functions.php`)

- **woo-custom.php** — WooCommerce support, product layout (no sidebar/tabs/upsells), sale badge off, quantity limits, VND formatting, breadcrumb, email headers  
- **thankyou-card.php** — Checkout “thank you card” fields, validation, save, display in admin and emails  
- **get-stock-pancake.php** — Inline JS + AJAX for product variation stock (Pancake), cache and UX  
- **pancake-stock.php** — Server-side `get_pancake_stock()`, AJAX handlers, checkout stock validation, cache clear on order  
- **pancake-loyalty.php** — Loyalty data from Pancake, My Account display, cache invalidation  
- **pancake-sync.php** — Send WooCommerce orders to Pancake on payment complete / processing / on-hold / completed  
- **pancake-webhook.php** — REST endpoint to receive Pancake order updates (signature verification)  
- **checkout-method.php** — Checkout payment method logic and PayPal request body filter  
- **checkout-state.php** — Custom `woocommerce_states` and country locale  
- **child-menu.php** — Child menu tree and AJAX endpoint for menu items  
- **purchased-products.php** — “Previously purchased” (or similar) block on single product; cache invalidation on order status  

Not loaded by default: `themes-option.php`, `paypal-base-currency.php`, `suggest-size.php` (size drawer is in `template-parts/suggest-size-drawer.php` and included from `footer.php`).

---

## Templates

- **Home:** `page-home.php` (Template Name: Home Page Template) — video slider + bestseller products  
- **Default page:** `page.php`, `page-blank-full-width.php`  
- **Blog/archive:** `index.php`, `archive.php`, `archive-portfolio.php`, `single.php`, `search.php`, `searchform.php`  
- **WooCommerce:** `woocommerce/archive-product.php`, `content-product.php`, `content-single-product.php`, `single-product/add-to-cart/variable.php`  
- **404:** `404.php`  
- **Parts:** header, footer, sidebar, comments; nav and suggest-size drawer in `template-parts/`

---

## Security & performance (v2.0)

- **Credentials:** Pancake keys read from `wp_config` constants only.  
- **Rate limiting:** `aesir_check_rate_limit()` for AJAX actions.  
- **Logging:** `aesir_log()` only when `WP_DEBUG` and `WP_DEBUG_LOG` are on.  
- **Assets:** Swiper and product/checkout scripts load only on relevant pages.  
- **Caching:** Pancake stock and loyalty use transients; cache cleared on order/webhook as needed.

---

## License

ISC (see `package.json`). Theme code may have its own terms; confirm in the repo.
