<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Plugin\CatalogSearch\Model\ResourceModel\Fulltext;

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

    public function beforeGetFacetedData(
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject,
        $field
    ): array {
        $this->helper->addLoginRequiredToCollection($subject);

        return [$field];
    }

    public function beforeLoad(
        \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $subject,
        $printQuery = false,
        $logQuery = false
    ): array {
        $this->helper->addLoginRequiredToCollection($subject);

        return [$printQuery, $logQuery];
    }
}
