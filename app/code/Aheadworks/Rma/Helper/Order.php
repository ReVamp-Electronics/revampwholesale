<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Helper;

/**
 * Class Order
 * @package Aheadworks\Rma\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Location of the "Return Period" config param
     */
    const XML_PATH_RETURN_PERIOD = 'aw_rma/general/return_period';

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\RequestItem\CollectionFactory
     */
    private $requestItemCollectionFactory;

    /**
     * @var \Aheadworks\Rma\Model\Source\Request\Status
     */
    private $sourceStatus;

    /**
     * @var array
     */
    private $requestCollections = [];

    /**
     * @var array
     */
    private $requestItemCollections = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory $requestCollectionFactory
     * @param \Aheadworks\Rma\Model\ResourceModel\RequestItem\CollectionFactory $requestItemCollectionFactory
     * @param \Aheadworks\Rma\Model\Source\Request\Status $sourceStatus
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory $requestCollectionFactory,
        \Aheadworks\Rma\Model\ResourceModel\RequestItem\CollectionFactory $requestItemCollectionFactory,
        \Aheadworks\Rma\Model\Source\Request\Status $sourceStatus
    ) {
        parent::__construct($context);
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->requestItemCollectionFactory = $requestItemCollectionFactory;
        $this->sourceStatus = $sourceStatus;
    }

    /**
     * Retrieves request items collection for given $orderId
     *
     * @param int $orderId
     * @param bool $onlyActive
     * @return \Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection
     */
    public function getAllRequestItems($orderId, $onlyActive = true)
    {
        $key = implode('-', [$orderId, $onlyActive]);
        if (!isset($this->requestItemCollections[$key])) {
            /** @var \Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection $collection */
            $collection = $this->requestItemCollectionFactory->create();
            $collection
                ->joinRequest()
                ->addOrderFilter($orderId)
            ;
            if ($onlyActive) {
                $collection->addRequestStatusFilter($this->sourceStatus->getActiveStatuses());
            }
            $this->requestItemCollections[$key] = $collection;
        }
        return $this->requestItemCollections[$key];
    }

    /**
     * Retrieves request collection for gived order item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param bool $onlyActive
     * @return \Aheadworks\Rma\Model\ResourceModel\Request\Collection
     */
    public function getAllRequestsForOrderItem(\Magento\Sales\Model\Order\Item $item, $onlyActive = true)
    {
        $key = implode('-', [$item->getId(), $onlyActive]);
        if (!isset($this->requestCollections[$key])) {
            $requestItemsCollection = $this->requestItemCollectionFactory->create()
                ->addOrderItemFilter($item->getId())
            ;
            $requestIds = [];
            foreach ($requestItemsCollection as $requestItem) {
                if (!in_array($requestItem->getRequestId(), $requestIds)) {
                    $requestIds[] = $requestItem->getRequestId();
                }
            }
            $requestCollection = $this->requestCollectionFactory->create()
                ->addFieldToFilter('id', ['in' => $requestIds])
            ;
            if ($onlyActive) {
                $requestCollection->addFieldToFilter('status_id', ['in' => $this->sourceStatus->getActiveStatuses()]);
            }
            $this->requestCollections[$key] = $requestCollection;
        }
        return $this->requestCollections[$key];
    }

    /**
     * Retrieves maximal order item count available for RMA
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    public function getItemMaxCount(\Magento\Sales\Model\Order\Item $item)
    {
        $max = 0;
        if ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            if ($item->getChildrenItems()) {
                foreach ($item->getChildrenItems() as $childrenItem) {
                    $childrenMax = $childrenItem->getQtyInvoiced() - $childrenItem->getQtyRefunded();
                    $requestItems = $this->getAllRequestItems($item->getData('order_id'));
                    foreach ($requestItems as $requestItem) {
                        if ($requestItem->getItemId() == $childrenItem->getId()) {
                            $childrenMax -= $requestItem->getQty();
                        }
                    }
                    $max += $childrenMax;
                }
            }
        } else {
            $max = $item->getQtyInvoiced() - $item->getQtyRefunded();
            $requestItems = $this->getAllRequestItems($item->getData('order_id'));
            foreach ($requestItems as $requestItem) {
                if ($requestItem->getItemId() == $item->getId()) {
                    $max -= $requestItem->getQty();
                }
            }
        }
        return max($max, 0);
    }

    /**
     * Check whether the given order is allowed for RMA
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isAllowedForOrder(\Magento\Sales\Model\Order $order)
    {
        if ($order->getState() == 'complete') {
            $returnPeriod = $this->scopeConfig->getValue(
                self::XML_PATH_RETURN_PERIOD,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );
            if (!$returnPeriod) {
                return true;
            }

            $lastInvoiceTime = 0;
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceTime = strtotime($invoice->getCreatedAt());
                if ($invoiceTime > $lastInvoiceTime) {
                    $lastInvoiceTime = $invoiceTime;
                }
            }
            if ($lastInvoiceTime && $lastInvoiceTime >= strtotime(sprintf("-%d day", $returnPeriod), time())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves order item product types, for which return creation is not allowed.
     * (Means, that it contains child items allowed for return)
     *
     * @return array
     */
    public function getNotReturnedOrderItemProductTypes()
    {
        return [
            \Magento\Bundle\Model\Product\Type::TYPE_CODE,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ];
    }
}