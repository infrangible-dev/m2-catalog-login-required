# Changelog

## 2.0.0

### Breaking changes

- Requires Magento 2.4.6 or newer and PHP 8.1 or newer. Older versions of Magento and PHP 7.1 to 8.0 are no longer
  supported; use version 1.x for these.
- Requires `infrangible/m2-core` 5.0, `infrangible/m2-foundation` 3.0 and `fewedev/base` 2.4; the module used
  `infrangible/m2-core` and `fewedev/base` before without requiring them.
- Guests are sent to the login page by plugins of Magento's controllers of category and product pages instead of the
  observers of `catalog_controller_category_init_after` and `catalog_controller_product_view`, which are removed.
- The module uses Magento's HTTP context value `customer_logged_in`; its own value `customer_is_logged_in` and the
  observer of `controller_action_predispatch` that set it are removed.
- The attributes are added by the data patch `LoginRequiredAttributes` instead of `Setup/InstallData.php`. Existing
  attributes and their values are kept.
- All classes have native types and use constructor property promotion; the constructors of `Helper\Data` and of the
  search data mapper changed.

### Fixed

- The redirect of guests to the login page contained the whole category or product page, with the products, prices
  and the add-to-cart form, which every client that does not follow the redirect could read. The page is no longer
  rendered for guests; the redirect is empty.

### Changed

- The data patch adds the attributes to the default group of every attribute set; version 1 added them to the
  attribute set "Default" only.
- The search index has the field `login_required` as number 0 or 1, instead of text for products with a value and a
  number for products without.

### Added

- Translations into 24 languages.
- Unit tests.
