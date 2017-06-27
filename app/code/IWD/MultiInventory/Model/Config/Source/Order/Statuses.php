<?php

namespace IWD\MultiInventory\Model\Config\Source\Order;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Status\Collection;

/**
 * Class Statuses
 * @package IWD\MultiInventory\Model\Config\Source\Order
 */
class Statuses implements ArrayInterface
{
    /**
     * @var string[]
     */
    private $options;

    /**
     * @var Collection
     */
    private $listStatus;

    /**
     * @param Collection $listStatus
     */
    public function __construct(Collection $listStatus)
    {
        $this->listStatus = $listStatus;
    }

    /**
     * Options getter
     * @return string[]
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            $statuses = $this->listStatus->getItems();
            foreach ($statuses as $status) {
                $id = $status->getData('status');
                $label = $status->getData('label');
                $this->options[] = ['value' => $id, 'label' => $label];
            }
        }
        return $this->options;
    }
}
