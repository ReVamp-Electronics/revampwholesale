<?php

namespace IWD\OrderManager\Model\Config\Source\Order;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Sales\Model\ResourceModel\Status\Collection;

/**
 * Class Statuses
 * @package IWD\OrderManager\Model\Config\Source\Order
 */
class Statuses implements ArrayInterface
{
    /**
     * @var string[]
     */
    protected $_options;

    /**
     * @var Collection
     */
    protected $_listStatus;

    /**
     * @param Collection $listStatus
     */
    public function __construct(Collection $listStatus)
    {
        $this->_listStatus = $listStatus;
    }

    /**
     * Options getter
     * @return string[]
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [];
            $statuses = $this->_listStatus->getItems();
            foreach ($statuses as $status) {
                $id = $status->getData('status');
                $label = $status->getData('label');
                $this->_options[] = ['value' => $id, 'label' => $label];
            }
        }
        return $this->_options;
    }
}
