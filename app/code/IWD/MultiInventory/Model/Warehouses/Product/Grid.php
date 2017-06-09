<?php

namespace IWD\MultiInventory\Model\Warehouses\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\CatalogInventory\Model\Stock;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;

/**
 * Class Grid
 * @package IWD\MultiInventory\Model\Warehouses\Product
 */
class Grid extends AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * Grid constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param MultiStockManagement $multiStockManagement
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        MultiStockManagement $multiStockManagement,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->multiStockManagement = $multiStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        $store = $this->getStore();
        $collection = $this->productFactory->create()->getCollection()->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'type_id'
        )->setStore(
            $store
        );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty' . Stock::DEFAULT_STOCK_ID,
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=' . Stock::DEFAULT_STOCK_ID,
                'left'
            );
            $collection->joinField(
                'is_in_stock_' . Stock::DEFAULT_STOCK_ID,
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=' . Stock::DEFAULT_STOCK_ID,
                'left'
            );

            $stocks = $this->multiStockManagement->getStocksList();
            foreach ($stocks as $stock) {
                $id = $stock['id'];
                $collection->joinField(
                    'qty' . $id,
                    'iwd_cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=' . $id,
                    'left'
                );
                $collection->joinField(
                    'is_in_stock_' . $id,
                    'iwd_cataloginventory_stock_item',
                    'is_in_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=' . $id,
                    'left'
                );
            }
        }

        return $collection;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    private function getStore()
    {
        $storeId = 0;
        return $this->storeManager->getStore($storeId);
    }
}
