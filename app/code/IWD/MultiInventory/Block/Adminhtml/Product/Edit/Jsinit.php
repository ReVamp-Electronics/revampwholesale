<?php

namespace IWD\MultiInventory\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Model\Source\Backorders as SourceBackorders;
use Magento\CatalogInventory\Model\Source\Stock as SourceStock;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\Framework\Registry;
use Magento\Framework\Api\SearchCriteriaBuilder;
use IWD\MultiInventory\Helper\Data;
use IWD\MultiInventory\Model\Warehouses\SourceRepository;
use IWD\MultiInventory\Model\Warehouses\SourceItemRepository;

/**
 * Class Jsinit
 * @package IWD\MultiInventory\Block\Adminhtml\Product\Edit
 */
class Jsinit extends Template
{
    /**
     * @var string
     */
    private $params;

    /**
     * @var SourceBackorders
     */
    private $sourceBackorders;

    /**
     * @var SourceStock
     */
    private $sourceStock;

    /**
     * @var StockItemInterface
     */
    private $stockItem;

    /**
     * @var StockItemCriteriaInterface
     */
    private $stockItemCriteria;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StockCriteriaInterfaceFactory
     */
    private $stockCriteriaFactory;

    /**
     * @var StockInterface
     */
    private $stock;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SourceRepository
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepository
     */
    private $sourceItemRepository;

    /**
     * @param Context $context
     * @param SourceBackorders $sourceBackorders
     * @param SourceStock $sourceStock
     * @param StockItemInterface $stockItem
     * @param StockItemCriteriaInterface $stockItemCriteria
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockRepositoryInterface $stockRepositoryInterface
     * @param StockCriteriaInterfaceFactory $stockCriteriaFactory
     * @param StockInterface $stock
     * @param Registry $coreRegistry
     * @param Data $helper
     * @param Configuration $configuration
     * @param SourceRepository $sourceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        SourceBackorders $sourceBackorders,
        SourceStock $sourceStock,
        StockItemInterface $stockItem,
        StockItemCriteriaInterface $stockItemCriteria,
        StockItemRepositoryInterface $stockItemRepository,
        StockRepositoryInterface $stockRepositoryInterface,
        StockCriteriaInterfaceFactory $stockCriteriaFactory,
        StockInterface $stock,
        Registry $coreRegistry,
        Data $helper,
        Configuration $configuration,
        SourceRepository $sourceRepository,
        SourceItemRepository $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceBackorders = $sourceBackorders;
        $this->sourceStock = $sourceStock;
        $this->stockItem = $stockItem;
        $this->stockItemCriteria = $stockItemCriteria;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockRepository = $stockRepositoryInterface;
        $this->stockCriteriaFactory = $stockCriteriaFactory;
        $this->coreRegistry = $coreRegistry;
        $this->stock = $stock;
        $this->helper = $helper;
        $this->configuration = $configuration;
        $this->sourceRepository = $sourceRepository;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return string
     */
    public function jsonProductInventoryStockData()
    {
        $this->prepareBackordersOption();
        $this->prepareStockOption();
        $this->prepareDefaultStocks();
        $this->prepareAdditionalStocks();
        $this->prepareProductOptions();

        return json_encode($this->params);
    }

    /**
     * @return void
     */
    private function prepareBackordersOption()
    {
        $this->params['backordersOption'] = $this->sourceBackorders->toOptionArray();
    }

    /**
     * @return void
     */
    private function prepareStockOption()
    {
        $this->params['stockOption'] = $this->sourceStock->toOptionArray();
    }

    /**
     * @return void
     */
    private function prepareProductOptions()
    {
        $product = $this->getProduct();
        $isComplexProduct = in_array(
            $product->getTypeId(),
            [
                \Magento\Bundle\Model\Product\Type::TYPE_CODE,
                \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
            ]
        );

        $this->params['isProductNew'] = $this->getProductId() == null;
        $this->params['isProductComposite'] = $product->isComposite();
        $this->params['isComplexProduct'] = $isComplexProduct;
        $this->params['canUseQtyDecimals'] = $product->getTypeInstance()->canUseQtyDecimals();
        $this->params['isVirtual'] = $product->isVirtual();
        $this->params['isReadonly'] = $product->isReadonly();
    }

    /**
     * @return void
     */
    private function prepareDefaultStocks()
    {
        $stocks = $this->getDefaultStock();
        $stockItems = $this->getDefaultStockItems();

        $items = isset($this->params['default_stocks']) ? $this->params['default_stocks'] : [];

        foreach ($stocks as $stock) {
            $id = $stock->getStockId();
            $items[$id] = $this->getStockItemDefault();
            $items[$id]['id'] = $id;
            $items[$id]['name'] = $stock->getStockName();
        }
        foreach ($stockItems as $stockItem) {
            $id = $stockItem->getStockId();
            $item = $stockItem->getData();
            $items[$id] = array_merge($items[$id], $item);
        }

        $this->params['default_stocks'] = array_values($items);
    }

    /**
     * @return void
     */
    private function prepareAdditionalStocks()
    {
        $stocks = $this->getAdditionalStocks();
        $stockItems = $this->getAdditionalStockItems();

        $items = isset($this->params['stocks']) ? $this->params['stocks'] : [];

        foreach ($stocks as $stock) {
            $id = $stock->getStockId();
            $items[$id] = $this->getStockItemDefault();
            $items[$id]['id'] = $id;
            $items[$id]['name'] = $stock->getStockName();
        }
        foreach ($stockItems as $stockItem) {
            $id = $stockItem->getStockId();
            $item = $stockItem->getData();
            $items[$id] = array_merge($items[$id], $item);
        }

        $this->params['stocks'] = array_values($items);
    }

    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Return current product id
     *
     * @return int
     */
    private function getProductId()
    {
        return $this->getProduct()->getEntityId();
    }

    /**
     * @return array
     */
    public function getStocks()
    {
        $defaultStocks = $this->getDefaultStock();
        $additionalStocks = $this->getAdditionalStocks();

        return array_merge($defaultStocks, $additionalStocks);
    }

    /**
     * @return \IWD\MultiInventory\Api\Data\SourceInterface[]
     */
    private function getAdditionalStocks()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $collection = $this->sourceRepository->getList($searchCriteria);

        return $collection->getItems();
    }

    /**
     * @return \IWD\MultiInventory\Api\Data\SourceItemInterface[]
     */
    public function getAdditionalStockItems()
    {
        $productId = $this->getProductId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('product_id', $productId)
            ->create();

        return $this->sourceItemRepository
            ->getList($searchCriteria)
            ->getItems();
    }

    /**
     * @return StockInterface[]
     */
    private function getDefaultStock()
    {
        $criteria = $this->stockCriteriaFactory->create();
        $collection = $this->stockRepository->getList($criteria);

        return $collection->getItems();
    }

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface[]
     */
    public function getDefaultStockItems()
    {
        $productId = $this->getProductId();
        $this->stockItemCriteria->setProductsFilter($productId);
        return $this->stockItemRepository
            ->getList($this->stockItemCriteria)
            ->getItems();
    }

    /**
     * @return array
     */
    public function getStockItemDefault()
    {
        $storeId = null;

        return [
            StockItemInterface::PRODUCT_ID => $this->stockItem->getProductId(),
            StockItemInterface::STOCK_ID => $this->stockItem->getStockId(),
            StockItemInterface::QTY => $this->stockItem->getQty(),
            StockItemInterface::IS_QTY_DECIMAL => $this->stockItem->getIsQtyDecimal(),
            StockItemInterface::MIN_QTY => $this->stockItem->getMinQty(),
            StockItemInterface::BACKORDERS => $this->stockItem->getBackorders(),
            StockItemInterface::MIN_SALE_QTY => $this->stockItem->getMinSaleQty(),
            StockItemInterface::MAX_SALE_QTY => $this->stockItem->getMaxSaleQty(),
            StockItemInterface::IS_IN_STOCK => $this->stockItem->getIsInStock(),
            StockItemInterface::LOW_STOCK_DATE => $this->stockItem->getLowStockDate(),
            StockItemInterface::NOTIFY_STOCK_QTY => $this->stockItem->getNotifyStockQty(),
            StockItemInterface::STOCK_STATUS_CHANGED_AUTO => $this->stockItem->getStockStatusChangedAuto(),
            StockItemInterface::QTY_INCREMENTS => $this->stockItem->getQtyIncrements(),
            StockItemInterface::ENABLE_QTY_INCREMENTS => $this->stockItem->getEnableQtyIncrements(),
            StockItemInterface::IS_DECIMAL_DIVIDED => $this->stockItem->getIsDecimalDivided(),
            StockItemInterface::MANAGE_STOCK => $this->stockItem->getManageStock(),
            StockItemInterface::USE_CONFIG_MIN_QTY => $this->stockItem->getUseConfigMinQty(),
            StockItemInterface::USE_CONFIG_BACKORDERS => $this->stockItem->getUseConfigBackorders(),
            StockItemInterface::USE_CONFIG_MIN_SALE_QTY => $this->stockItem->getUseConfigMaxSaleQty(),
            StockItemInterface::USE_CONFIG_MAX_SALE_QTY => $this->stockItem->getUseConfigMaxSaleQty(),
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY => $this->stockItem->getUseConfigNotifyStockQty(),
            StockItemInterface::USE_CONFIG_QTY_INCREMENTS => $this->stockItem->getUseConfigQtyIncrements(),
            StockItemInterface::USE_CONFIG_ENABLE_QTY_INC => $this->stockItem->getUseConfigEnableQtyInc(),
            StockItemInterface::USE_CONFIG_MANAGE_STOCK => $this->stockItem->getUseConfigManageStock(),
            'config_min_qty' => $this->configuration->getMinQty($storeId),
            'config_backorders' => $this->configuration->getBackorders($storeId),
            'config_min_sale_qty' => $this->configuration->getMinSaleQty($storeId),
            'config_max_sale_qty' => $this->configuration->getMaxSaleQty($storeId),
            'config_notify_stock_qty' => $this->configuration->getNotifyStockQty($storeId),
            'config_qty_increments' => $this->configuration->getQtyIncrements($storeId),
            'config_enable_qty_inc' => $this->configuration->getEnableQtyIncrements($storeId),
            'config_manage_stock' => $this->configuration->getManageStock($storeId)
        ];
    }

    /**
     * @return bool|int
     */
    public function isExtensionEnabled()
    {
        return $this->helper->isExtensionEnabled();
    }
}
