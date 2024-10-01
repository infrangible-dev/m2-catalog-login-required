<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ControllerActionPredispatch implements ObserverInterface
{
    /** @var Session */
    protected $customerSession;

    /** @var Context */
    protected $httpContext;

    public function __construct(Session $customerSession, Context $httpContext)
    {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    public function execute(Observer $observer): void
    {
        $this->httpContext->setValue(
            'customer_is_logged_in',
            $this->customerSession->isLoggedIn(),
            false
        );
    }
}
