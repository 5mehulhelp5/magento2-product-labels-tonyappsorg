# Product Labels for Magento 2

Show badges such as "New", "−20%" or "Free gift" on product listings and product pages, driven by rules you set once.

Magento Open Source has catalog price rules, but no general way to put a promotional badge on a product. This module adds label rules that use the same product conditions as catalog price rules, so badges appear automatically on every product that matches.

## Features

- **Rule-driven badges**: choose which products get a label with Magento's familiar catalog-rule condition builder (attribute set, category, SKU, any attribute usable in catalog rules).
- **Your text, your colors**: each label has short text (up to 80 characters), a background color and a text color.
- **Everywhere shoppers browse**: labels show on category and search result product cards and on the product page.
- **Several labels at once**: when more than one rule matches, all labels show, in the sort order you set.
- **Per store view**: limit a rule to selected store views, or show it everywhere.
- **Switch on and off**: activate or deactivate a rule without deleting it.
- **Pages update by themselves**: saving or deleting a rule refreshes the page cache, so shoppers see the change straight away.

## How it works

1. In the admin, open **Content › Product Labels** and click **Add New Label Rule**.
2. Enter the label text and colors, choose store views and sort order, and set the product conditions.
3. Save. Matching products show the badge on category pages, search results and their product page.

## Good to know

- Labels are text you write: a "−20%" badge doesn't calculate or guarantee a discount.
- A rule needs at least one condition; a rule without conditions shows nowhere.
- Conditions look at product data only, not cart contents.
- "Low stock" labels work only through product attributes available to catalog rules; live salable quantity and configurable child stock are not used.
- Colors are entered as hex codes (`#RRGGBB`).
- Labels are not shown in product widgets, cart, or cross-sell/related blocks.
- Built and tested on the Luma theme; Hyvä has not been checked.

## Requirements

- Magento Open Source 2.4.8
- PHP 8.3 or 8.4
- Magento modules: Catalog, CatalogRule

## Installation

```sh
composer require tonyapps/module-product-labels
bin/magento module:enable Tonyapps_ProductLabels
bin/magento setup:upgrade
```

## Configuration

- **Content › Product Labels**: create, edit and delete label rules (admin permission: *Product Labels*).

## For developers

Architecture, decisions, data model and tests are in [AGENTS.md](AGENTS.md).
