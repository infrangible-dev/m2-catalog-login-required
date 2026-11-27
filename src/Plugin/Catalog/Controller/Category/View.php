<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Plugin\Catalog\Controller\Category;

use Infrangible\CatalogLoginRequired\Helper\Data;
use Magento\Catalog\Controller\Category\View as CategoryView;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;

/**
 * Sends guests to the login page before a category that requires a login is loaded and rendered.
 */
class View
{
    /**
     * @param Data $helper
     * @param CategoryResource $categoryResource
     */
    public function __construct(protected Data $helper, protected CategoryResource $categoryResource)
    {
    }

    /**
     * Returns the redirect to the login page for guests if the category requires a login, else shows the category.
     *
     * @param CategoryView $subject
     * @param callable $proceed
     *
     * @return ResultInterface|ResponseInterface
     * @throws NoSuchEntityException if there is no current store
     * @throws SessionException if the session cannot be started
     */
    public function aroundExecute(CategoryView $subject, callable $proceed): ResultInterface|ResponseInterface
    {
        return $this->helper->redirectGuestToLogin($subject->getRequest(), $this->categoryResource) ?? $proceed();
    }
}
