<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Product;

use \Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Grid
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Product
 */
class Grid extends Container
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'IWD_MultiInventory::warehouse/product/grid.phtml';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_blockGroup = 'IWD_MultiInventory';
        $this->_controller = 'adminhtml_warehouses_product';
        $this->_headerText = __('Source');

        parent::_construct();

        $this->removeButton('add');
        $this->addUpdateButton();
    }

    /**
     * @return void
     */
    private function addUpdateButton()
    {
        $this->addButton(
            'add',
            [
                'label' => __('Update'),
                'onclick' => 'iwdWarehouseProductUpdate(\'' . $this->getUrl('*/*/update') . '\')',
                'class' => 'add primary'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Grid',
                'grid.view.grid'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
