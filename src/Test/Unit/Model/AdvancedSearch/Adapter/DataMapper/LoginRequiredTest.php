<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\CatalogLoginRequired\Test\Unit\Model\AdvancedSearch\Adapter\DataMapper;

use FeWeDev\Base\Variables;
use Infrangible\CatalogLoginRequired\Model\AdvancedSearch\Adapter\DataMapper\LoginRequired;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the field "login_required" of the products in the search index.
 */
class LoginRequiredTest extends TestCase
{
    /**
     * @throws LocalizedException
     */
    public function testFieldsAsNumbers(): void
    {
        $attributeHelper = $this->createStub(Attribute::class);
        $attributeHelper->method('getAttributeValues')->willReturn([1 => '1', 2 => '0', 3 => null]);

        $databaseHelper = $this->createStub(Database::class);
        $databaseHelper->method('getDefaultConnection')->willReturn($this->createStub(AdapterInterface::class));

        $dataMapper = new LoginRequired(new Variables(), $attributeHelper, $databaseHelper);

        $this->assertSame(
            [
                1 => ['login_required' => 1],
                2 => ['login_required' => 0],
                3 => ['login_required' => 0],
                4 => ['login_required' => 0]
            ],
            $dataMapper->getFields([1, 2, 3, 4], '1')
        );
    }
}
