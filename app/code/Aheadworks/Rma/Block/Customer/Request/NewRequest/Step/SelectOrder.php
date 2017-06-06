<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step;

/**
 * Class SelectOrder
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class SelectOrder extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEXT_PAGE_BLOCK = 'aw_rma/blocks_and_policy/product_selection_block';

    /**
     * @var string
     */
    protected $_template = 'customer/request/newrequest/step/selectorder.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Aheadworks\Rma\Helper\CmsBlock
     */
    private $cmsBlockHelper;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    private $orderHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|null
     */
    private $orderCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->cmsBlockHelper = $cmsBlockHelper;
        $this->orderHelper = $orderHelper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @return string
     */
    public function getTextCmsBlockHtml()
    {
        return $this->cmsBlockHelper->getBlockHtml(self::XML_PATH_TEXT_PAGE_BLOCK);
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if ($this->orderCollection === null) {
            $this->orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()])
                ->setOrder('created_at', 'desc')
            ;
            if ($this->getReturnPeriod() > 0) {
                $this->orderCollection->getSelect()
                    ->where('updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)', $this->getReturnPeriod())
                ;
            }
        }
        return $this->orderCollection;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getOrderInfo(\Magento\Sales\Model\Order $order)
    {
        if (!$this->orderHelper->isAllowedForOrder($order)) {
            return __('Can\'t create return for this order');
        }
        return '';
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isAllowedForOrder(\Magento\Sales\Model\Order $order)
    {
        return $this->orderHelper->isAllowedForOrder($order);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getItemRenderer(\Magento\Sales\Model\Order\Item $orderItem)
    {
        return $this->getLayout()
            ->createBlock('Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\SelectOrder\Items\Renderer')
            ->setItem($orderItem)
        ;
    }

    /**
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->_scopeConfig->getValue(
            \Aheadworks\Rma\Helper\Order::XML_PATH_RETURN_PERIOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getNoOrdersMessage()
    {
        if ($this->getReturnPeriod() > 0) {
            $message = __(
                'You have no completed orders to request RMA or your orders were placed more than %1 days ago.',
                $this->getReturnPeriod()
            );
        } else {
            $message = __('You have no completed orders to request RMA');
        }
        return $message;
    }

    /**
     * @param float $amount
     * @return string
     */
    public function convertAndFormatPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/createRequestStep');
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Retrieves saved request data
     *
     * @return array
     */
    public function getCurrentRequestData()
    {
        return $this->customerSession->getOrderSelectData();
    }

    /**
     * Retrieves selected order ID
     *
     * @return int
     */
    public function getCurrentOrderId()
    {
        $currentOrderId = 0;
        if ($requestData = $this->getCurrentRequestData()) {
            $currentOrderId = $requestData['order_id'];
        }
        return $currentOrderId;
    }

    /**
     * Retrieves selected order items and its quantities
     *
     * @return array|null
     */
    public function getCurrentRequestItems()
    {
        $currentRequestItems = null;
        $requestData = $this->getCurrentRequestData();
        if ($requestData && isset($requestData['item']) && is_array($requestData['item'])) {
            $currentRequestItems = $requestData['item'];
        }
        return $currentRequestItems;
    }
}
