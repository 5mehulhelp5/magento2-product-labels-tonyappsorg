<?php
declare(strict_types=1);

namespace Tonyapps\ProductLabels\Test\Unit\View;

use PHPUnit\Framework\TestCase;

class LayoutLocationTest extends TestCase
{
    /** Magento only reads storefront layouts from view/frontend/layout. */
    public function testStorefrontLayoutsAreWhereMagentoLoadsThem(): void
    {
        $root = __DIR__ . '/../../..';
        foreach (['catalog_product_view.xml', 'default.xml'] as $file) {
            self::assertFileExists($root . '/view/frontend/layout/' . $file);
        }
        self::assertDirectoryDoesNotExist($root . '/etc/frontend/layout');
    }

    /** Hyvä's product page moves the block defined for Luma, so both files must agree on its name. */
    public function testHyvaLayoutMovesTheLumaBlock(): void
    {
        $root = __DIR__ . '/../../../view/frontend/layout/';
        $luma = simplexml_load_file($root . 'catalog_product_view.xml');
        $hyva = simplexml_load_file($root . 'hyva_catalog_product_view.xml');
        $moved = (string)$hyva->xpath('//move')[0]['element'];

        self::assertNotEmpty($luma->xpath(sprintf('//block[@name="%s"]', $moved)));
        self::assertSame('product.info.title', (string)$hyva->xpath('//move')[0]['destination']);
    }
}
