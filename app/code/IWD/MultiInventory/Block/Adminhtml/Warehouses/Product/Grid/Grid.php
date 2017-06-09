<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid;

use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use Magento\CatalogInventory\Model\Stock;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Grid
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    private $productType;

    /**
     * @var \IWD\MultiInventory\Model\Warehouses\Product\Grid
     */
    private $gridFactory;

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \IWD\MultiInventory\Model\Warehouses\Product\Grid $gridFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param MultiStockManagement $multiStockManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \IWD\MultiInventory\Model\Warehouses\Product\Grid $gridFactory,
        \Magento\Catalog\Model\Product\Type $type,
        MultiStockManagement $multiStockManagement,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->productType = $type;
        $this->gridFactory = $gridFactory;
        $this->multiStockManagement = $multiStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('iwd_catalog_stock');
        $this->setDefaultSort('grid_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->gridFactory->getCollection();

        $this->setCollection($collection);
        $this->getCollection()->addWebsiteNamesToResult();

        parent::_prepareCollection();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Name'),
                'index' => 'name',
                'renderer' => 'IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render\Name'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );

        $store = $this->getStore();
        if ($store->getId()) {
            $this->addColumn(
                'custom_name',
                [
                    'header' => __('Name in %1', $store->getName()),
                    'index' => 'custom_name',
                    'header_css_class' => 'col-name',
                    'column_css_class' => 'col-name'
                ]
            );
        }

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->productType->getOptionArray()
            ]
        );

        $this->addColumn(
            'qty',
            [
                'header' => __('Default Qty'),
                'index' => 'qty' . Stock::DEFAULT_STOCK_ID,
                'type' => 'number',
                'renderer' => 'IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render\Qty'
            ]
        );

        $stocks = $this->getStockCollection();
        foreach ($stocks as $stock) {
            $id = $stock['id'];
            $this->addColumn(
                'qty' . $id,
                [
                    'header' => $stock['stockName'],
                    'index' => 'qty' . $id,
                    'width' => '50px',
                    'type' => 'number',
                    'renderer' => 'IWD\MultiInventory\Block\Adminhtml\Warehouses\Product\Grid\Render\Qty'
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    private function getStockCollection()
    {
        return $this->multiStockManagement->getStocksList();
    }

    /**
     * @return StoreInterface
     */
    private function getStore()
    {
        $storeId = 0;
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url')
            ? $this->_getData('grid_url')
            : $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
