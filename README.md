# Infrangible Catalog Login Required

Restricts selected categories and products to logged-in customers: guests who open them are sent to the login page and
return to the page after logging in, and products that require a login are hidden from guests in product lists and
search results.

## Settings

- **Login Required** on categories (section "General" of the category form) and on products, per store view.

There are no store settings.

## Behavior

- Guests who open a category or product with "Login Required" are redirected to the login page before the page is
  rendered; after logging in, they return to the page.
- Products with "Login Required" are hidden from guests in the product lists of categories, in search results and in
  the counts of the layered navigation. Hiding them uses the catalog search index, so run
  `bin/magento indexer:reindex catalogsearch_fulltext` after installing the module.
- Categories with "Login Required" are still shown in the navigation.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer
- PHP 8.1 or newer

## Installation

```bash
composer require infrangible/m2-catalog-login-required
bin/magento module:enable Infrangible_CatalogLoginRequired
bin/magento setup:upgrade
bin/magento indexer:reindex catalogsearch_fulltext
```

In production mode, also run `bin/magento setup:di:compile` and `bin/magento setup:static-content:deploy`.

## License

Infrangible Catalog Login Required is licensed under the MIT License - see the LICENSE file for details.
