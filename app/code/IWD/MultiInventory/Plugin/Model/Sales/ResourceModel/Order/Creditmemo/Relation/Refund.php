<?php

namespace IWD\MultiInventory\Plugin\Model\Sales\ResourceModel\Order\Creditmemo\Relation;

/**
 * Class Refund
 * @package IWD\MultiInventory\Plugin\Model\Sales\ResourceModel\Order\Creditmemo\Relation
 */
class Refund
{
    /**
     * @param $subject
     * @param $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    public function aroundProcessRelation($subject, $proceed, \Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getData('disable_after_save_event') == true) {
            return;
        }

        $proceed($object);
    }
}
