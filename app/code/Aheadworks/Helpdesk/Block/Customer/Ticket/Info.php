<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Customer\Ticket;

/**
 * Class Info
 * @package Aheadworks\Helpdesk\Block\Customer\Ticket
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Order model factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Order Model Factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResourceModel;

    /**
     * Ticket status source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->orderFactory = $orderFactory;
        $this->orderResourceModel = $orderResource;
        $this->statusSource = $statusSource;
        parent::__construct($context, $data);
    }

    /**
     * Get current ticket
     * @return \Aheadworks\Helpdesk\Model\Ticket
     */
    public function getTicket()
    {
        return $this->coreRegistry->registry('aw_helpdesk_ticket');
    }

    /**
     * Get current external key
     * @return string
     */
    public function getExternalKey()
    {
        return $this->coreRegistry->registry('aw_helpdesk_key');
    }

    /**
     * Return close ticket url
     *
     * @param int $ticketId
     * @return string
     */
    public function getTicketCloseUrl($ticketId)
    {
        if ($this->getExternalKey()) {
            $ticketCloseUrl = $this->getUrl(
                'aw_helpdesk/ticket/close',
                ['key' => $this->getExternalKey(), '_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            $ticketCloseUrl = $this->getUrl(
                'aw_helpdesk/ticket/close',
                ['id' => $ticketId, '_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $ticketCloseUrl;
    }

    /**
     * Return order view url
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl(
            'sales/order/view',
            [
                'order_id' => $orderId,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Get order increment id
     * @param int $orderId
     * @return string
     */
    public function getOrderIncrementId($orderId)
    {
        $orderModel = $this->orderFactory->create();
        $this->orderResourceModel->load($orderModel, $orderId);
        return $orderModel->getIncrementId();
    }

    /**
     * Get ticket status label
     * @return mixed
     */
    public function getTicketStatusLabel()
    {
        $statusValue = $this->getTicket()->getStatus();
        return $this->statusSource->getOptionLabelByValue($statusValue);
    }

    /**
     * Return close ticket url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('aw_helpdesk/ticket/index', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
