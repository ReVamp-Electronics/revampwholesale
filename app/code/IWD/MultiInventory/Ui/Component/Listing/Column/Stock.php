<?php

namespace IWD\MultiInventory\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use IWD\MultiInventory\Model\Warehouses\MultiStockManagement;
use IWD\MultiInventory\Helper\Data as MultiStockHelper;
use Magento\Ui\Model\BookmarkManagement;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Class Stock
 * @package IWD\MultiInventory\Ui\Component\Listing\Column
 */
class Stock extends Column
{
    /**
     * @var MultiStockManagement
     */
    private $multiStockManagement;

    /**
     * @var MultiStockHelper
     */
    private $multiStockHelper;

    /**
     * @var BookmarkRepositoryInterface
     */
    private $bookmarkRepository;

    /**
     * @var BookmarkManagement
     */
    private $bookmarkManagement;

    /**
     * Stock constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param MultiStockManagement $multiStockManagement
     * @param MultiStockHelper $multiStockHelper
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagement $bookmarkManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MultiStockManagement $multiStockManagement,
        MultiStockHelper $multiStockHelper,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagement $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->multiStockManagement = $multiStockManagement;
        $this->multiStockHelper = $multiStockHelper;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;

        $this->disableMultiStockColumn();
    }

    private function disableMultiStockColumn()
    {
        $config = $this->getData('config');
        $config['controlVisibility'] = $this->multiStockHelper->isExtensionEnabled();
        $config['visible'] = $this->multiStockHelper->isExtensionEnabled();
        $this->setData('config', $config);

        if (!$this->multiStockHelper->isExtensionEnabled()) {
            $bookmarks = $this->bookmarkManagement->loadByNamespace('sales_order_grid');
            foreach ($bookmarks->getItems() as $bookmark) {
                $config = $bookmark->getConfig();
                $this->deleteItem($config, 'iwd_stock_assigned');
                $config = json_encode($config);
                $bookmark->setConfig($config);
                $this->bookmarkRepository->save($bookmark);
            }
        }
    }

    /**
     * @param $array
     * @param $value
     */
    private function deleteItem(&$array, $value)
    {
        foreach ($array as $key => $val) {
            if ($key === $value) {
                unset($array[$key]);
            } elseif (is_array($val)) {
                $this->deleteItem($array[$key], $value);
            }
        }
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items']) && $this->multiStockHelper->isExtensionEnabled()) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orderId = $item['entity_id'];
                $this->multiStockManagement->loadOrder($orderId);
                $item['assignedQty'] = $this->multiStockManagement->getOrderQtyAssigned();
                $item['orderedQty'] = $this->multiStockManagement->getOrderQtyOrdered();
                $item['refundedQty'] = $this->multiStockManagement->getOrderRefundedQty();
                $item['isOrderPlacesBefore'] = $this->multiStockManagement->getIsOrderPlacedBeforeInit();
                $item['isNotApplicable'] = $this->multiStockManagement->getIsOrderStockNotApplicable();
                $item['id'] = $item['entity_id'];
            }
        }

        return $dataSource;
    }
}
