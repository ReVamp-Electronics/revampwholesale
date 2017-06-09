<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Info;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;
use IWD\OrderManager\Model\Order\Order;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Info
 */
class Form extends AbstractForm
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Status\Collection
     */
    private $listStatus;

    /**
     * @var \Magento\Sales\Model\Config\Source\Order\Status
     */
    private $status;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @var \IWD\OrderManager\Model\Salesrep\Salesrep
     */
    private $salesrep;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Status\Collection $listStatus
     * @param \Magento\Sales\Model\Config\Source\Order\Status $status
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \IWD\OrderManager\Model\Salesrep\Salesrep $salesrep
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Status\Collection $listStatus,
        \Magento\Sales\Model\Config\Source\Order\Status $status,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \IWD\OrderManager\Model\Salesrep\Salesrep $salesrep,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->listStatus = $listStatus;
        $this->status = $status;
        $this->orderConfig = $orderConfig;
        $this->salesrep = $salesrep;
        //$this->timezone = $timezone;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getStatusList()
    {
        return $this->orderConfig->getStatuses();
    }

    /**
     * @return string[]
     */
    public function getStateList()
    {
        return [
           \Magento\Sales\Model\Order::STATE_NEW => __('New'),
           \Magento\Sales\Model\Order::STATE_PROCESSING => __('Processing'),
           \Magento\Sales\Model\Order::STATE_COMPLETE => __('Complete'),
           \Magento\Sales\Model\Order::STATE_CLOSED => __('Closed'),
           \Magento\Sales\Model\Order::STATE_CANCELED => __('Canceled'),
           \Magento\Sales\Model\Order::STATE_HOLDED => __('On Hold'),
           \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => __('Payment Review'),
           \Magento\Sales\Model\Order::STATUS_FRAUD => __('Suspected Fraud')
        ];
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsiteList()
    {
        return $this->_storeManager->getWebsites();
    }

    /**
     * @return bool
     */
    public function hasSingleStore()
    {
        return $this->_storeManager->hasSingleStore();
    }

    /**
     * @return bool
     */
    public function getAllowChangeState()
    {
        return $this->_scopeConfig->getValue('iwdordermanager/order_info/edit_state');
    }

    /**
     * @return false|string
     */
    public function getCreatedAt()
    {
        $createdAt = $this->getOrder()->getCreatedAt();
        $date = new \DateTime($createdAt, new \DateTimeZone('UTC'));
        //TODO: fix logic
        //$timezone = new \DateTimeZone($this->timezone->getConfigTimezone('store', $this->getOrder()->getStore()));
        //$date->setTimezone($timezone);
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return bool
     */
    public function isSalesRepEnabled()
    {
        return $this->salesrep->isSalesRepEnabled();
    }

    /**
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getSalesReps()
    {
        return $this->salesrep->getSalesReps();
    }

    /**
     * @return int
     */
    public function getCurrentSalesReps()
    {
        $orderId = $this->getOrder()->getId();
        return $this->salesrep->getCurrentSalesReps($orderId);
    }
}
