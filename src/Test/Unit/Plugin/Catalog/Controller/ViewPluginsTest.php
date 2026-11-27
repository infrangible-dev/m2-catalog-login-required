<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Test\Unit\Plugin\Catalog\Controller;

use Infrangible\CatalogLoginRequired\Helper\Data;
use Infrangible\CatalogLoginRequired\Plugin\Catalog\Controller\Category\View as CategoryPlugin;
use Infrangible\CatalogLoginRequired\Plugin\Catalog\Controller\Product\View as ProductPlugin;
use Magento\Catalog\Controller\Category\View as CategoryView;
use Magento\Catalog\Controller\Product\View as ProductView;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the category and product pages are not rendered for guests if they require a login.
 */
class ViewPluginsTest extends TestCase
{
    /**
     * Returns the helper that returns the redirect for guests or null.
     *
     * @param ResponseInterface|null $redirect
     *
     * @return Data
     */
    private function createHelper(?ResponseInterface $redirect): Data
    {
        $helper = $this->createStub(Data::class);
        $helper->method('redirectGuestToLogin')->willReturn($redirect);

        return $helper;
    }

    /**
     * Returns the controller of category pages with a request.
     *
     * @return CategoryView
     */
    private function createCategoryView(): CategoryView
    {
        $view = $this->createStub(CategoryView::class);
        $view->method('getRequest')->willReturn($this->createStub(RequestInterface::class));

        return $view;
    }

    /**
     * Returns the controller of product pages with a request.
     *
     * @return ProductView
     */
    private function createProductView(): ProductView
    {
        $view = $this->createStub(ProductView::class);
        $view->method('getRequest')->willReturn($this->createStub(RequestInterface::class));

        return $view;
    }

    /**
     * Returns a rendered page.
     *
     * @return ResultInterface
     */
    private function createPage(): ResultInterface
    {
        return $this->createStub(ResultInterface::class);
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function testRedirectInsteadOfPage(): void
    {
        $redirect = $this->createStub(ResponseInterface::class);
        $rendered = false;
        $proceed = function () use (&$rendered): ResultInterface {
            $rendered = true;

            return $this->createPage();
        };

        $helper = $this->createHelper($redirect);
        $categoryPlugin = new CategoryPlugin($helper, $this->createStub(CategoryResource::class));
        $productPlugin = new ProductPlugin($helper, $this->createStub(ProductResource::class));

        $this->assertSame($redirect, $categoryPlugin->aroundExecute($this->createCategoryView(), $proceed));
        $this->assertSame($redirect, $productPlugin->aroundExecute($this->createProductView(), $proceed));
        $this->assertFalse($rendered);
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function testPageWithoutRestriction(): void
    {
        $page = $this->createStub(ResultInterface::class);
        $proceed = fn(): ResultInterface => $page;

        $helper = $this->createHelper(null);
        $categoryPlugin = new CategoryPlugin($helper, $this->createStub(CategoryResource::class));
        $productPlugin = new ProductPlugin($helper, $this->createStub(ProductResource::class));

        $this->assertSame($page, $categoryPlugin->aroundExecute($this->createCategoryView(), $proceed));
        $this->assertSame($page, $productPlugin->aroundExecute($this->createProductView(), $proceed));
    }
}
