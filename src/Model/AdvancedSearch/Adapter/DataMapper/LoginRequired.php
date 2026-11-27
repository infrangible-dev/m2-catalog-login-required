<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Model\AdvancedSearch\Adapter\DataMapper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogLoginRequired\Helper\Data;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * Adds the field "login_required" to the products in the search index: 1 if the product requires a login, else 0.
 */
class LoginRequired implements AdditionalFieldsProviderInterface
{
    /**
     * @param Variables $variables
     * @param Attribute $attributeHelper
     * @param Database $databaseHelper
     */
    public function __construct(
        protected Variables $variables,
        protected Attribute $attributeHelper,
        protected Database $databaseHelper
    ) {
    }

    /**
     * Returns the field "login_required" of the products in the store.
     *
     * @param array $productIds
     * @param int|string $storeId
     *
     * @return array
     * @phpstan-param array<int|string> $productIds
     * @phpstan-return array<int|string, array{login_required: int}>
     * @throws LocalizedException if the attribute does not exist
     */
    public function getFields(array $productIds, $storeId): array
    {
        $attributeValues = $this->attributeHelper->getAttributeValues(
            $this->databaseHelper->getDefaultConnection(),
            Product::ENTITY,
            Data::ATTRIBUTE_CODE,
            $productIds,
            $this->variables->intValue($storeId)
        );

        $fields = [];

        foreach ($productIds as $productId) {
            $loginRequired = $attributeValues[ $productId ] ?? 0;

            $fields[ $productId ] = [Data::ATTRIBUTE_CODE => is_numeric($loginRequired) ? (int)$loginRequired : 0];
        }

        return $fields;
    }
}
