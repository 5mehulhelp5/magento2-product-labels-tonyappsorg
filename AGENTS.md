# Tonyapps_ProductLabels — notes for AI agents and developers

Read this before changing the module. Workspace-wide rules are in ../AGENTS.md.

## Status

Package `tonyapps/module-product-labels` 1.0.0. WORKPLAN shortlist #2 (quick win): "built, manual QA pending".

| Phase | Scope | Status |
|---|---|---|
| 1. Rule engine and storefront | Catalog-rule conditions, scoped rule storage/admin editing, matching and ordered badges on listing and detail views, focused unit tests. | Built. |
| 2. Manual verification | Install/upgrade, create representative rules, inspect category/search and product pages, verify cache invalidation and multiple-label ordering. | Partly done 2026-11-08: two rules created by script, labels and ordering verified on listing and product page; **admin form untested** (no panel access). See ../WORKPLAN.md. |

Why it exists: Magento Open Source provides catalog price rules, but it has no general product-label feature for promotional text such as "New", "−20%", "Low stock", or "Free gift".

## Scope and decisions

| Feature | Decision |
|---|---|
| Rule conditions | Reuse Magento's catalog-rule product condition builder. Conditions are evaluated against the catalog product, not cart contents. |
| Badge content | Each rule has a short text label, foreground/background colors, sort order, active flag, and store-view scope. |
| Display | Show all matching labels on category/search product cards and product detail pages. Lower sort order appears first. |
| Rule conflict | No exclusivity: several rules may match and all render in configured order. |
| Cache behavior | Labels depend on product data, active rule configuration, and store view. Rule saves (and deletes) clear the full-page and block HTML caches. |
| Inventory | "Low stock" is supported only through product attributes that are available to catalog rules. Dynamic salable quantity and configurable child inventory are out of phase 1. |
| Price | A displayed "−20%" is administrator-authored text; labels do not calculate or promise a discount. |
| Security | Admin CRUD is ACL-protected and POST-only for mutations. Escape label text and validate CSS colors before output. |

## Architecture

- Admin: menu **Content > Product Labels** (`Tonyapps_ProductLabels::rules`, route `tonyapps_productlabels/rule/index`). Controllers in `Controller/Adminhtml/Rule/` extend `AbstractRule` (ACL + `cleanCaches()` → `cleanType('block_html')`, `cleanType('full_page')`): `Index`, `Edit`, `NewAction` (GET); `Save`, `Delete`, `NewConditionHtml` (POST). Grid is a plain table (`Block\Adminhtml\Rule\Index`, `rule/index.phtml`: Name, Badge, Status, Sort Order, Edit/Delete with JS confirm); form `Block\Adminhtml\Rule\Form`, `rule/form.phtml` ("Save Label Rule").
- Model: `Model\Rule` (catalog-rule conditions; `getStoreIds()`; `beforeSave()`), resource `Model\ResourceModel\Rule` + `Rule\Collection` (`addActiveFilter()`), `Model\ColorValidator` (`/^#[0-9A-Fa-f]{6}$/`, uppercased; otherwise null).
- Matching: `Model\RuleMatcher::matches()` — store filter (empty `store_ids` = all), then rule must have at least one condition and `validate($product)`. `Model\LabelProvider` loads active rules once per request (`getRules()` memoised), returns `label`/`background_color`/`text_color` per matching rule; `getConditionAttributeCodes()` collects attributes used in conditions.
- Listing: `Plugin\Catalog\Product\ListProductLabelsPlugin::afterGetProductDetailsHtml()` on `Magento\Catalog\Block\Product\AbstractProduct` (frontend `di.xml`) appends badges to each product card; it acts only on `ListProduct` (Luma) and on the block named `product_list_item` (Hyvä renders every listing item with that `Product\View` block). `Observer\AddConditionAttributesObserver` on `catalog_block_product_list_collection` (frontend `events.xml`) adds condition attributes to the listing collection, dropping codes that no longer exist.
- Product page: `Block\ProductLabels` + `product/labels.phtml` in `product.info.main` before `product.info.price` (`catalog_product_view.xml`). CSS `css/product-labels.css` added in `default.xml` (sizes in px: Luma and Hyvä root font sizes differ). Hyvä: `hyva_catalog_product_view.xml` moves the block into `product.info.title` (Hyvä's `product-info.phtml` renders named children only; the title renders all its children) and adds a second copy, `tonyapps.product.labels.mobile`, to `page.main.title` with `css_class` `flex justify-center md:hidden`, because Hyvä shows one title per breakpoint. `css_class` goes on a wrapper div: Hyvä's Tailwind utilities sit in a CSS `@layer` and lose to this module's unlayered CSS on the same element.
- Empty `ProductLabels/Adminhtml/` directory at module root (no files).

## Data model

`tonyapps_product_label_rule`: `rule_id` (PK), `name` (255, admin name), `label` (80, badge text), `background_color` (7, default `#333333`), `text_color` (7, default `#FFFFFF`), `is_active` (default 1), `sort_order` (default 0), `store_ids` (text, comma-separated store view IDs; empty = all), `conditions_serialized` (mediumtext), `created_at`, `updated_at`; index (`is_active`, `sort_order`). `etc/db_schema_whitelist.json` present.

## Configuration and ACL

- No system config section.
- ACL `Tonyapps_ProductLabels::rules` ("Product Labels") under `Magento_Backend::content`.

## Decisions and constraints

See Scope and decisions. Plus:
- Condition attributes are added only to product-listing collections, not every frontend product collection (cart, cross-sells).
- Performance: active rules loaded once per request; the `category_ids` condition still runs one cached core lookup per product.

## Known limitations and open points

- Labels render only on `ListProduct`-based listings (category, search) and the product page; not in widgets, related/up-sell/cross-sell blocks or cart.
- A rule with no conditions never matches.
- Admin form not yet verified by hand (Status).
- Hyvä: supported since 2026-11-16 (see Architecture). On Hyvä, related/up-sell sliders also get badges, because Hyvä renders them with the same `product_list_item` block as the category listing.

## Tests

```sh
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Tonyapps/ProductLabels/Test/Unit
```

Unit tests: ColorValidator, LabelProvider, RuleMatcher, AddConditionAttributesObserver, ListProductLabelsPlugin, layout location (incl. the Hyvä move). Playwright: `../e2e/tests/product-labels.spec.js` (Luma) and `../e2e/tests/hyva-product-labels.spec.js` (Hyvä store view, `hyva.riskified.ddev.site`).

## Operations

```sh
bin/magento module:enable Tonyapps_ProductLabels
bin/magento setup:upgrade
```

## Change history

- **2026-11-16** — Hyvä support: listing plugin moved from `ListProduct` to `AbstractProduct` (limited to listing items), `hyva_catalog_product_view.xml` places the product-page labels under the title, label CSS in px. Luma unchanged.
- **2026-11-16** — Released as free software under GPL-3.0-or-later (owner decision): `LICENSE` added,
  `composer.json` license changed from `proprietary`, License section in the README.
- **2026-10-30 review fixes**:
  - A rule condition on an attribute that was later deleted no longer causes a 500 on every storefront product collection. Attribute codes that no longer exist are dropped before `addAttributeToSelect()`.
  - Condition attributes are added only to product-listing collections (the `catalog_block_product_list_collection` event), not to every frontend product collection such as the cart or cross-sells.
  - Active rules are loaded once per request instead of once per product. The `category_ids` condition still runs one cached core lookup per product.
