# Facefood Laravel API (reference for FF Addons)

Base URL (production): `https://app.facefood.cafe/api`

FF Addons reads the **public** catalog endpoints below. Auth / staff / WordPress sync routes are listed for reuse when you extend the plugin — they are **not** required for the default Elementor / WPBakery widgets.

Source of truth in the monorepo: `backend/routes/api.php`.

---

## Public (no auth)

| Method | Path | Notes |
|--------|------|-------|
| GET | `/settings` | Branding / store settings |
| GET | `/categories` | Menu categories |
| GET | `/categories/{category}` | Single category |
| GET | `/products` | Products (`per_page`, filters) |
| GET | `/products/{product}` | Single product |
| GET | `/products/{product}/reviews` | Product reviews |
| GET | `/deals` | Active deals |
| GET | `/deals/{deal}` | Single deal |
| POST | `/orders/quote` | Delivery / cart quote |
| POST | `/client-errors` | Crash / client error reports (throttled) |

### Example — products

```bash
curl -sS 'https://app.facefood.cafe/api/products?per_page=8' \
  -H 'Accept: application/json'
```

Typical JSON shape (paginated Laravel resource):

```json
{
  "data": [
    {
      "id": 1,
      "name": "Zinger Burger",
      "description": "...",
      "price": 650,
      "image_url": "https://..."
    }
  ]
}
```

### Example — deals

```bash
curl -sS 'https://app.facefood.cafe/api/deals?per_page=6' \
  -H 'Accept: application/json'
```

### Example — settings (connectivity check)

```bash
curl -sS 'https://app.facefood.cafe/api/settings' \
  -H 'Accept: application/json'
```

---

## Auth (Sanctum)

Used by the Flutter customer app and the main `facefood-integration` WordPress plugin. Not used by FF Addons widgets by default.

| Method | Path | Notes |
|--------|------|-------|
| POST | `/login`, `/register`, … | See `backend/routes/api-auth.php` |
| GET/PUT/PATCH | `/user` | Profile |
| CRUD | `/addresses` | Saved addresses |
| GET | `/recommendations/order-again` | Reorder suggestions |
| GET/POST/DELETE | `/favorites` | Favorites |
| GET/POST | `/orders` | Customer orders |
| POST | `/products/{product}/reviews` | Submit review |

Send `Authorization: Bearer {token}` and `Accept: application/json`.

---

## WordPress sync (header auth)

Used by **facefood-integration** (WooCommerce sync), not by FF Addons display widgets.

Header: `X-Facefood-Sync-Key: YOUR_KEY` (must match Laravel `WORDPRESS_SYNC_KEY`).

| Method | Path |
|--------|------|
| GET | `/wordpress/catalog` |
| POST | `/wordpress/link` |
| POST | `/wordpress/bulk-link` |
| POST | `/wordpress/products/upsert` |
| POST | `/wordpress/orders/upsert` |
| PUT/PATCH | `/wordpress/settings/branding` |

```bash
curl -I -X POST https://app.facefood.cafe/api/wordpress/orders/upsert
# Expect 401/422 when key missing — not 404
```

---

## Staff (Sanctum + staff role)

Prefix: `/staff` — dashboard, orders, riders, FCM, portal data. Used by `facefood_staff_app`.

---

## Admin panel API (Sanctum + permissions)

Prefix: `/admin` — manage products, categories, deals, settings. Used by internal tools.

---

## FF Addons usage

| Setting | Default |
|---------|---------|
| API base URL | `https://app.facefood.cafe/api` |
| Cache TTL | `300` seconds (WordPress transients) |

Widgets / shortcodes call:

- `GET /products`
- `GET /deals`
- `GET /settings` (admin connectivity probe only)

Shortcodes:

```
[ff_addons_menu limit="8" columns="4" category=""]
[ff_addons_deals limit="6" columns="3"]
[ff_addons_cta title="Order Facefood" button="Order now" url="/"]
```

Elementor category: **FF Addons** (FF Menu, FF Deals, FF CTA).  
WPBakery category: **FF Addons** (same three elements).
