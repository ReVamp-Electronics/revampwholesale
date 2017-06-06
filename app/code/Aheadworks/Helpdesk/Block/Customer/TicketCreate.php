<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Customer;

use Aheadworks\Helpdesk\Model\Source\Ticket\DepartmentFrontend as DepartmentFrontendSource;

/**
 * Customer Ticket create form block
 * Class TicketCreate
 * @package Aheadworks\Helpdesk\Block\Customer
 */
class TicketCreate extends \Magento\Framework\View\Element\Template
{
    /**
     * Order collection factory
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * Current customer
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * Order config
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * Order collection
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $orders;

    /**
     * @var DepartmentFrontendSource
     */
    private $departmentFrontendSource;

    /**
     * @var string[]
     */
    private $departments;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param DepartmentFrontendSource $departmentFrontendSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Sales\Model\Order\Config $orderConfig,
        DepartmentFrontendSource $departmentFrontendSource,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->currentCustomer = $currentCustomer;
        $this->orderConfig = $orderConfig;
        $this->departmentFrontendSource = $departmentFrontendSource;
        parent::__construct($context, $data);
    }

    /**
     * Get orders as array
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrdersAsOptionArray()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory->create()->addFieldToSelect(
                ['id' => 'entity_id', 'name' => 'increment_id']
            )->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter(
                'status',
                ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            )->toOptionHash();
        }
        return $this->orders;
    }

    /**
     * Check order_id parameter in case of referring from order view page
     *
     * @param int $orderId
     * @return bool
     */
    public function isPreselectedOrder($orderId)
    {
        return $orderId == $this->getRequest()->getParam('order_id');
    }

    /**
     * Return create ticket url
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('aw_helpdesk/ticket/save', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get departments as array
     * @return bool|string[]
     */
    public function getDepartments()
    {
        if (!($this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->departments) {
            $this->departments = $this->departmentFrontendSource->getOptions();
        }
        return $this->departments;
    }
}
