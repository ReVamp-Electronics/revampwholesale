<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterUpdateCheckoutCart implements ObserverInterface
{
    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     */
    public function __construct(
        \MW\RewardPoints\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_dataHelper->setEmptyRewardpoint();

        return $this;
    }
}
