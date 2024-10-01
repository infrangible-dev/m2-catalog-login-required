<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Model\AdvancedSearch\Adapter\DataMapper;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class LoginRequired implements AdditionalFieldsProviderInterface
{
    /** @var Database */
    protected $databaseHelper;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var Arrays */
    protected $arrays;

    /** @var Variables */
    protected $variables;

    public function __construct(
        Arrays $arrays,
        Variables $variables,
        Attribute $attributeHelper,
        Database $databaseHelper
    ) {
        $this->arrays = $arrays;
        $this->variables = $variables;
        $this->attributeHelper = $attributeHelper;
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getFields(array $productIds, $storeId): array
    {
        $dbAdapter = $this->databaseHelper->getDefaultConnection();

        $attributeValues = $this->attributeHelper->getAttributeValues(
            $dbAdapter,
            Product::ENTITY,
            'login_required',
            $productIds,
            $this->variables->intValue($storeId)
        );

        $fields = [];

        foreach ($productIds as $productId) {
            $loginRequired = $this->arrays->getValue(
                $attributeValues,
                $this->variables->stringValue($productId),
                0
            );

            if ($loginRequired === null) {
                $loginRequired = 0;
            }

            $fields[ $productId ] = ['login_required' => $loginRequired];
        }

        return $fields;
    }
}
