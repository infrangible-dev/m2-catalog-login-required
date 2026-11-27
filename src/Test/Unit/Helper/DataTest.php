<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Test\Unit\Helper;

use Infrangible\CatalogLoginRequired\Helper\Data;
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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests hiding products from guests and sending guests to the login page.
 */
class DataTest extends TestCase
{
    /** The ID of the category or product that requires a login. */
    private const RESTRICTED_ID = 7;

    /** @var list<string> the calls of the customer session */
    private array $sessionCalls = [];

    /**
     * Returns the helper for a guest or a logged-in customer.
     *
     * @param bool $loggedIn
     * @param ResponseInterface|null $response
     *
     * @return Data
     */
    private function createHelper(bool $loggedIn, ?ResponseInterface $response = null): Data
    {
        $httpContext = $this->createStub(Context::class);
        $httpContext->method('getValue')->willReturnCallback(
            fn(string $name): bool => $name === CustomerContext::CONTEXT_AUTH && $loggedIn
        );

        $customerSession = $this->createStub(Session::class);
        $customerSession->method('isLoggedIn')->willReturn($loggedIn);
        $customerSession->method('setAfterAuthUrl')->willReturnCallback(
            function (string $url) use ($customerSession): Session {
                $this->sessionCalls[] = 'after auth url ' . $url;

                return $customerSession;
            }
        );
        $customerSession->method('authenticate')->willReturnCallback(
            function (): bool {
                $this->sessionCalls[] = 'authenticate';

                return false;
            }
        );

        $url = $this->createStub(UrlInterface::class);
        $url->method('getCurrentUrl')->willReturn('https://example.com/bag.html');

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(3);

        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        return new Data(
            $httpContext,
            $customerSession,
            $url,
            $storeManager,
            $response ?? $this->createStub(ResponseInterface::class)
        );
    }

    /**
     * Returns a resource model whose entity with the restricted ID requires a login in store 3.
     *
     * @return AbstractResource
     */
    private function createResource(): AbstractResource
    {
        $resource = $this->createStub(AbstractResource::class);
        $resource->method('getAttributeRawValue')->willReturnCallback(
            fn(int $entityId, string $attribute, int $storeId): string|false => $attribute === Data::ATTRIBUTE_CODE &&
            $storeId === 3 ? ($entityId === self::RESTRICTED_ID ? '1' : '0') : false
        );

        return $resource;
    }

    /**
     * Returns a request with the ID.
     *
     * @param string|null $id
     *
     * @return RequestInterface
     */
    private function createRequest(?string $id): RequestInterface
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('getParam')->willReturnCallback(fn(string $name): ?string => $name === 'id' ? $id : null);

        return $request;
    }

    public function testCollectionOfGuestsWithoutRestrictedProducts(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('hasFlag')->willReturn(false);
        $collection->expects($this->once())->method('addAttributeToFilter')->with(
            Data::ATTRIBUTE_CODE,
            [['eq' => 0], ['null' => null]],
            'left'
        );
        $collection->expects($this->once())->method('addFieldToFilter')->with(Data::ATTRIBUTE_CODE, 0);
        $collection->expects($this->once())->method('setFlag')->with('catalog_login_required', true);

        $this->createHelper(false)->addLoginRequiredToCollection($collection);
    }

    public function testCollectionOfCustomersUnfiltered(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('hasFlag')->willReturn(false);
        $collection->expects($this->never())->method('addAttributeToFilter');
        $collection->expects($this->never())->method('addFieldToFilter');

        $this->createHelper(true)->addLoginRequiredToCollection($collection);
    }

    public function testCollectionFilteredOnce(): void
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('hasFlag')->willReturn(true);
        $collection->expects($this->never())->method('addFieldToFilter');

        $this->createHelper(false)->addLoginRequiredToCollection($collection);
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function testGuestSentToLogin(): void
    {
        $response = $this->createStub(ResponseInterface::class);

        $result = $this->createHelper(false, $response)->redirectGuestToLogin(
            $this->createRequest((string)self::RESTRICTED_ID),
            $this->createResource()
        );

        $this->assertSame($response, $result);
        $this->assertSame(['after auth url https://example.com/bag.html', 'authenticate'], $this->sessionCalls);
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function testNoRedirectWithoutRestriction(): void
    {
        $helper = $this->createHelper(false);
        $resource = $this->createResource();

        $this->assertNull($helper->redirectGuestToLogin($this->createRequest('8'), $resource));
        $this->assertNull($helper->redirectGuestToLogin($this->createRequest(null), $resource));
        $restrictedRequest = $this->createRequest((string)self::RESTRICTED_ID);

        $this->assertNull($this->createHelper(true)->redirectGuestToLogin($restrictedRequest, $resource));
        $this->assertSame([], $this->sessionCalls);
    }
}
