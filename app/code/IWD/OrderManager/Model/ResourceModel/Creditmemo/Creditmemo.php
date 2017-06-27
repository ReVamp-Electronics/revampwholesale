<?php

namespace IWD\OrderManager\Model\ResourceModel\Creditmemo;

use Magento\Sales\Model\ResourceModel\Order\Creditmemo as MageCreditmemo;

/**
 * Class Creditmemo
 * @package IWD\OrderManager\Model\ResourceModel\Creditmemo
 */
class Creditmemo extends MageCreditmemo
{
    /**
     * @var bool
     */
    private $disableAfterSaveEvent = false;

    /**
     * @return void
     */
    public function disableAfterSaveEvent()
    {
        $this->disableAfterSaveEvent = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function processAfterSaves(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->disableAfterSaveEvent) {
            $object->setData('disable_after_save_event', true);
        } else {
            parent::processAfterSaves($object);
        }
    }
}
