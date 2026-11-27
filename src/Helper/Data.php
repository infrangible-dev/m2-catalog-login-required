<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Helper;

use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks if categories and products require a login, sends guests to the login page and hides such products from the
 * product lists and search results of guests.
 */
class Data
{
    /** The code of the attribute of categories and products that requires a login. */
    public const ATTRIBUTE_CODE = 'login_required';

    /** The flag of collections that are already filtered. */
    private const COLLECTION_FLAG = 'catalog_login_required';

    /**
     * @param Context $httpContext
     * @param Session $customerSession
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     */
    public function __construct(
        protected Context $httpContext,
        protected Session $customerSession,
        protected UrlInterface $url,
        protected StoreManagerInterface $storeManager,
        protected ResponseInterface $response
    ) {
    }

    /**
     * Filters the products that require a login out of the collection, if the customer is not logged in.
     *
     * @param Collection $collection
     *
     * @return void
     */
    public function addLoginRequiredToCollection(Collection $collection): void
    {
        if ($collection->hasFlag(self::COLLECTION_FLAG)) {
            return;
        }

        if (! $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
            $collection->addAttributeToFilter(
                self::ATTRIBUTE_CODE,
                [['eq' => 0], ['null' => null]],
                'left'
            );

            $collection->addFieldToFilter(self::ATTRIBUTE_CODE, 0);
        }

        $collection->setFlag(self::COLLECTION_FLAG, true);
    }

    /**
     * Sends guests to the login page if the category or product with the ID of the request requires a login.
     *
     * Returns the response with the redirect, null for logged-in customers and other categories and products.
     *
     * @param RequestInterface $request
     * @param AbstractResource $resource the resource model of categories or products
     *
     * @return ResponseInterface|null
     * @throws NoSuchEntityException if there is no current store
     * @throws SessionException if the session cannot be started
     */
    public function redirectGuestToLogin(RequestInterface $request, AbstractResource $resource): ?ResponseInterface
    {
        $entityId = $request->getParam('id');

        if (! is_numeric($entityId) || $this->customerSession->isLoggedIn()) {
            return null;
        }

        $loginRequired = $resource->getAttributeRawValue(
            (int)$entityId,
            self::ATTRIBUTE_CODE,
            $this->storeManager->getStore()->getId()
        );

        if (! is_scalar($loginRequired) || ! $loginRequired) {
            return null;
        }

        $this->customerSession->setAfterAuthUrl($this->url->getCurrentUrl());
        $this->customerSession->authenticate();

        return $this->response;
    }
}
