<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Setup;

use Infrangible\Core\Helper\Setup;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Validator\ValidateException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallData implements InstallDataInterface
{
    /** @var Setup */
    protected $setupHelper;

    /** @var EavSetupFactory */
    protected $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory, Setup $setupHelper)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setupHelper = $setupHelper;
    }

    /**
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $this->setupHelper->addEavEntityAttribute(
            $eavSetup,
            Category::ENTITY,
            'login_required',
            'Login Required',
            ScopedAttributeInterface::SCOPE_STORE,
            'int',
            'boolean',
            1000,
            '0'
        );

        $this->setupHelper->addAttributeToSetAndGroup(
            $eavSetup,
            Category::ENTITY,
            'login_required',
            'Default',
            'Default'
        );

        $this->setupHelper->addEavEntityAttribute(
            $eavSetup,
            Product::ENTITY,
            'login_required',
            'Login Required',
            ScopedAttributeInterface::SCOPE_STORE,
            'int',
            'boolean',
            1000,
            '0',
            false,
            false,
            true
        );

        $this->setupHelper->addAttributeToSetAndGroup(
            $eavSetup,
            Product::ENTITY,
            'login_required',
            'Default',
            'Default'
        );

        $setup->endSetup();
    }
}
