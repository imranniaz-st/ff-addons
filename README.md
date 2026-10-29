# FF Addons

Facefood WordPress add-ons for **Elementor** and **WPBakery** (js_composer). Lightweight catalog / CTA blocks powered by the Facefood Laravel API (`https://app.facefood.cafe/api`).

This plugin is a **separate** project from:

- `facefood-wordpress` / `facefood-integration` (full WooCommerce + sync)
- the Facefood Laravel backend

## Requirements

- WordPress 6.0+, PHP 8.1+
- Elementor (optional) and/or WPBakery Page Builder (optional)
- Outbound HTTPS to `app.facefood.cafe`

## Install

1. Clone or download into `wp-content/plugins/ff-addons`
2. Activate **FF Addons** in wp-admin
3. **Settings → FF Addons** — confirm API base URL
4. Drag widgets in Elementor / WPBakery, or use shortcodes

## Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[ff_addons_menu]` | Product grid |
| `[ff_addons_deals]` | Deals grid |
| `[ff_addons_cta]` | Call-to-action banner |

## API docs

See [docs/API.md](docs/API.md) for public, auth, WordPress sync, staff, and admin endpoints.

## Deploy (Hostinger SSH + CI)

Push to `main` runs PHP lint, then SSH-deploys to `facefood.cafe` (symlink into `wp-content/plugins/ff-addons`).

Setup: [.github/DEPLOY_SETUP.md](.github/DEPLOY_SETUP.md)

```bash
bash scripts/setup-ssh.sh
ssh hostinger-facefood "echo ok"
```

## License

Proprietary — Facefood.
