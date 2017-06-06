<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Customer;

/**
 * Customer Tickets list block
 * Class TicketList
 * @package Aheadworks\Helpdesk\Block\Customer
 */
class TicketList extends \Magento\Framework\View\Element\Template
{
    /**
     * Tickets collection
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Collection
     */
    protected $collection;

    /**
     * Ticket resource model
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Ticket statuses
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $ticketStatusSource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $ticketStatusSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $ticketStatusSource,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->currentCustomer = $currentCustomer;
        $this->ticketStatusSource = $ticketStatusSource;
        parent::__construct($context, $data);
    }

    /**
     * Get html code for toolbar
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Initializes toolbar
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getTickets()) {
            $toolbar = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'customer_ticket_list.toolbar'
            )->setCollection(
                $this->getTickets()
            );

            $this->setChild('toolbar', $toolbar);
        }
        return parent::_prepareLayout();
    }

    /**
     * Get tickets
     *
     * @return bool|\Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Collection
     */
    public function getTickets()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        $customer = $this->currentCustomer->getCustomer();
        $storeIds = $this->_storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection
                ->joinTicketFlat()
                ->addCustomerFilter($customer, $storeIds)
                ->orderByLastReply()
            ;
        }
        return $this->collection;
    }

    /**
     * Format date
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->_localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::SHORT
        );
    }

    /**
     * Return ticket view url
     *
     * @param $ticketId
     * @return string
     */
    public function getTicketViewUrl($ticketId)
    {
        return $this->getUrl('aw_helpdesk/ticket/view', ['id' => $ticketId, '_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Return order view url
     *
     * @param integer $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get ticket status label
     * @param $ticketStatus
     * @return string
     */
    public function getTicketStatusLabel($ticketStatus)
    {
        return $this->ticketStatusSource->getOptionLabelByValue($ticketStatus);
    }
}
