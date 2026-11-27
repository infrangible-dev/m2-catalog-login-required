<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Plugin\CatalogSearch\Model\ResourceModel\Fulltext;

use Infrangible\CatalogLoginRequired\Helper\Data;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextCollection;

/**
 * Hides the products that require a login from the product lists, search results and their filters for guests.
 */
class Collection
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Filters the products that require a login out of the collection before the filter values are counted.
     *
     * @param FulltextCollection $subject
     *
     * @return void
     */
    public function beforeGetFacetedData(FulltextCollection $subject): void
    {
        $this->helper->addLoginRequiredToCollection($subject);
    }

    /**
     * Filters the products that require a login out of the collection before it is loaded.
     *
     * @param FulltextCollection $subject
     *
     * @return void
     */
    public function beforeLoad(FulltextCollection $subject): void
    {
        $this->helper->addLoginRequiredToCollection($subject);
    }
}
