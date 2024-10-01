<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Http\Context;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var Context */
    protected $httpContext;

    public function __construct(Context $httpContext)
    {
        $this->httpContext = $httpContext;
    }

    public function addLoginRequiredToCollection(Collection $collection): void
    {
        $flag = 'catalog_login_required';

        if (! $collection->hasFlag($flag)) {
            if (! $this->httpContext->getValue('customer_is_logged_in')) {
                $collection->addAttributeToFilter(
                    'login_required',
                    [['eq' => 0], ['null' => null]],
                    'left'
                );

                $collection->addFieldToFilter(
                    'login_required',
                    0
                );
            }

            $collection->setFlag(
                $flag,
                true
            );
        }
    }
}
