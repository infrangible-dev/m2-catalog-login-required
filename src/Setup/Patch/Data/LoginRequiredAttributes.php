<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Setup\Patch\Data;

use Infrangible\CatalogLoginRequired\Helper\Data;
use Infrangible\Core\Helper\Setup;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Validator\ValidateException;

/**
 * Adds the attribute "login_required" to categories and products, in the default group of every attribute set.
 *
 * Installations of version 1 already have the attributes; the patch adds them to the attribute sets, which version 1
 * did for the default attribute set only.
 */
class LoginRequiredAttributes implements DataPatchInterface
{
    /** The position of the attributes in their group. */
    private const SORT_ORDER = 1000;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param Setup $setupHelper
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected CategorySetupFactory $categorySetupFactory,
        protected Setup $setupHelper
    ) {
    }

    /**
     * Adds the attributes, if they do not exist, and adds them to the attribute sets.
     *
     * @return $this
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): static
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ([Category::ENTITY => false, Product::ENTITY => true] as $entityType => $usedInProductListing) {
            if (! $categorySetup->getAttributeId($entityType, Data::ATTRIBUTE_CODE)) {
                $this->setupHelper->addEavEntityAttribute(
                    $categorySetup,
                    $entityType,
                    Data::ATTRIBUTE_CODE,
                    'Login Required',
                    ScopedAttributeInterface::SCOPE_STORE,
                    'int',
                    'boolean',
                    self::SORT_ORDER,
                    '0',
                    false,
                    false,
                    $usedInProductListing
                );
            }

            $this->addToAttributeSets($categorySetup, $entityType);
        }

        return $this;
    }

    /**
     * Adds the attribute to the default group of every attribute set of the entity type.
     *
     * @param CategorySetup $categorySetup
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    protected function addToAttributeSets(CategorySetup $categorySetup, string $entityType): void
    {
        $entityTypeId = $categorySetup->getEntityTypeId($entityType);

        foreach ($categorySetup->getAllAttributeSetIds($entityTypeId) as $attributeSetId) {
            $this->setupHelper->addAttributeToSetAndGroup(
                $categorySetup,
                $entityType,
                Data::ATTRIBUTE_CODE,
                (string)$attributeSetId,
                (string)$categorySetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId),
                self::SORT_ORDER
            );
        }
    }

    /**
     * Returns the patches that have to run first: none.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Returns other names of the patch: none.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
