<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DataObject;

/**
 * Class Qty
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render
 */
class Qty extends AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(DataObject $row)
    {
        $productId = $row->getData('entity_id');
        $stockId = str_replace('qty', '', $this->getColumn()->getIndex());
        $qty = $row->getData($this->getColumn()->getIndex()) * 1;

        $isInStock = $row->getData('is_in_stock_' . $stockId) ? 'checked="checked"' : '';
        $classInputQty = (Stock::DEFAULT_STOCK_ID == $stockId) ? 'inventory_qty_default' : 'inventory_qty';

        $qtyInput = '';

        if (!in_array($row->getData('type_id'), ['configurable', 'bundle', 'grouped'])) {
            $qtyInput = sprintf(
                "<input class='product-qty input-text admin__control-text %s' type='text' name='stock[%s][%s][qty]' value='%s' title='Qty'/>",
                $classInputQty,
                $productId,
                $stockId,
                $qty
            );
        }

        return sprintf(
            "<div class='product-stock-cell'>
                %s
                <div class='product-in-stock' title='In Stock'>
                    <input class='product_in_stock admin__control-checkbox' type='checkbox' %s name='stock[%s][%s][is_in_stock]' value='1'/>
                    <label></label>
                </div>
            </div>",
            $qtyInput,
            $isInStock,
            $productId,
            $stockId
        );
    }
}
