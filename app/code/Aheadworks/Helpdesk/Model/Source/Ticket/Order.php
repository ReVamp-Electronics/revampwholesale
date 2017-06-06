<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source\Ticket;


/**
 * Class Order
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Order
{
    /**
     * Order collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        return $this;
    }

    const UNASSIGNED_VALUE = '0';
    const UNASSIGNED_LABEL = "Unassigned";

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArrayByCustomerData($customerId, $customerEmail)
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        if ($customerId) {
            $whereCondition = "(main_table.customer_id = {$customerId} OR main_table.customer_email = '{$customerEmail}')";
        } else {
            $whereCondition = "(main_table.customer_email = '{$customerEmail}')";
        }

        $orderCollection
            ->getSelect()
            ->where($whereCondition)
            ->columns([
                'order_id' => 'main_table.entity_id',
                'increment_id' => 'main_table.increment_id'
            ]);
        $orderCollection->addOrder('created_at');

        $orderArr = [];
        foreach($orderCollection->getItems() as $item) {
            $orderArr[$item->getOrderId()] = $item->getIncrementId();
        }
        $result = [self::UNASSIGNED_VALUE => __(self::UNASSIGNED_LABEL)];
        return $result + $orderArr;
    }

}
