<?php

declare(strict_types=1);

namespace Infrangible\CatalogLoginRequired\Observer;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\UrlInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogControllerCategoryInitAfter implements ObserverInterface
{
    /** @var Session */
    protected $customerSession;

    /** @var UrlInterface */
    protected $urlInterface;

    public function __construct(Session $customerSession, UrlInterface $urlInterface)
    {
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @throws SessionException
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        /** @var Category $category */
        $category = $event->getData('category');

        if (! $this->customerSession->isLoggedIn() && $category->getData('login_required')) {
            $this->customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $this->customerSession->authenticate();
        }
    }
}
