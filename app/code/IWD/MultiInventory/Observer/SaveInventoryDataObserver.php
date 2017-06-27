<?php

namespace IWD\MultiInventory\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use IWD\MultiInventory\Helper\Data;
use IWD\MultiInventory\Api\SourceItemRepositoryInterfaceFactory;

class SaveInventoryDataObserver implements ObserverInterface
{
    /**
     * @var SourceItemRepositoryInterfaceFactory
     */
    private $sourceItemRepository;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    public function __construct(
        StockIndexInterface $stockIndex,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository,
        SourceItemRepositoryInterfaceFactory $sourceItemRepository,
        Data $helper
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->helper = $helper;
        $this->sourceItemRepository = $sourceItemRepository;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->isExtensionEnabled()) {
            $product = $observer->getEvent()->getProduct();
            $this->saveStockItemsData($product);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    private function saveStockItemsData($product)
    {
        $stockItemData = $product->getIwdStockData();
        $productId = $product->getId();

        if (empty($stockItemData) || !is_array($stockItemData)) {
            return $this;
        }

        foreach ($stockItemData as $stockId => $stockItem) {
            if (!is_array($stockItem)) {
                continue;
            }

            $websiteId = isset($stockItem['website_id'])
                ? $stockItem['website_id']
                : $this->stockConfiguration->getDefaultScopeId();

            $qty = isset($stockItem['qty']) ? $stockItem['qty'] : 0;
            $isInStock = isset($stockItem['qty']) ? $stockItem['is_in_stock'] : 0;
            $manageStock = isset($stockItem['qty']) ? $stockItem['manage_stock'] : 0;

            $sourceItemRepository = $this->sourceItemRepository->create();
            $item = $sourceItemRepository->getItem($productId, $stockId);
            $item->setQty($qty)
                ->setWebsiteId($websiteId)
                ->setIsInStock($isInStock)
                ->setManageStock($manageStock)
                ->setProductId($productId)
                ->setStockId($stockId);
            $sourceItemRepository->save($item);
        }

        return $this;
    }
}
