<?php

namespace IWD\OrderManager\Plugin\Model\Sales\ResourceModel\Order\Handler;

/**
 * Class State
 * @package IWD\OrderManager\Plugin\Model\Sales\ResourceModel\Order\Handler
 * @see \Magento\Sales\Model\ResourceModel\Order\Handler\State
 */
class State
{
    /**
     * @param $subject
     * @param $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function aroundCheck($subject, $proceed, \Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getData('disable_save_handler') == true) {
            return;
        }

        $proceed($object);
    }
}
