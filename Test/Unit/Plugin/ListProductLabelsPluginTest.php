<?php
declare(strict_types=1);

namespace Tonyapps\ProductLabels\Test\Unit\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;
use Tonyapps\ProductLabels\Model\LabelProvider;
use Tonyapps\ProductLabels\Plugin\Catalog\Product\ListProductLabelsPlugin;

/** Listing badges: Luma's ListProduct and Hyvä's product_list_item block get them, other product blocks do not. */
class ListProductLabelsPluginTest extends TestCase
{
    private ListProductLabelsPlugin $plugin;

    protected function setUp(): void
    {
        $provider = $this->createMock(LabelProvider::class);
        $provider->method('getLabels')->willReturn([['label' => 'Sale', 'background_color' => '#D32F2F', 'text_color' => '#FFFFFF']]);
        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnArgument(0);
        $escaper->method('escapeHtmlAttr')->willReturnArgument(0);
        $this->plugin = new ListProductLabelsPlugin($provider, $escaper);
    }

    public function testLumaListingGetsBadges(): void
    {
        $html = $this->plugin->afterGetProductDetailsHtml($this->createMock(ListProduct::class), '', $this->createMock(Product::class));
        self::assertStringContainsString('>Sale</span>', $html);
    }

    public function testHyvaListItemGetsBadges(): void
    {
        $block = $this->createMock(View::class);
        $block->method('getNameInLayout')->willReturn('product_list_item');
        $html = $this->plugin->afterGetProductDetailsHtml($block, '', $this->createMock(Product::class));
        self::assertStringContainsString('>Sale</span>', $html);
    }

    public function testOtherProductBlocksAreLeftAlone(): void
    {
        $block = $this->createMock(View::class);
        $block->method('getNameInLayout')->willReturn('product.info');
        self::assertSame('x', $this->plugin->afterGetProductDetailsHtml($block, 'x', $this->createMock(Product::class)));
    }
}
