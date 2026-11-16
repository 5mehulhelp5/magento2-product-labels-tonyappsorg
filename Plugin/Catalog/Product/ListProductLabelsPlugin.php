<?php
declare(strict_types=1);

namespace Tonyapps\ProductLabels\Plugin\Catalog\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Tonyapps\ProductLabels\Model\LabelProvider;

class ListProductLabelsPlugin
{
    public function __construct(private readonly LabelProvider $labelProvider, private readonly Escaper $escaper)
    {
    }

    /**
     * Luma renders listing items in ListProduct; Hyvä renders each item in a separate block named product_list_item.
     * Other product blocks (the product page itself) are left alone.
     */
    public function afterGetProductDetailsHtml(AbstractProduct $subject, string $html, Product $product): string
    {
        if (!$subject instanceof ListProduct && $subject->getNameInLayout() !== 'product_list_item') {
            return $html;
        }
        $labels = $this->labelProvider->getLabels($product);
        if (!$labels) {
            return $html;
        }
        $badges = '<div class="tonyapps-product-labels">';
        foreach ($labels as $label) {
            $style = 'background-color:' . $this->escaper->escapeHtmlAttr($label['background_color'])
                . ';color:' . $this->escaper->escapeHtmlAttr($label['text_color']);
            $badges .= '<span class="tonyapps-product-label" style="' . $style . '">'
                . $this->escaper->escapeHtml($label['label']) . '</span>';
        }
        return $html . $badges . '</div>';
    }
}
