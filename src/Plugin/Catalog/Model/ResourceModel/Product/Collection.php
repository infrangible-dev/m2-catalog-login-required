<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Plugin\Catalog\Model\ResourceModel\Product;

use Infrangible\CatalogLoginRequired\Helper\Data;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection
{
    /** @var Data */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ): array {
        $this->helper->addLoginRequiredToCollection($subject);

        return [$printQuery, $logQuery];
    }
}
