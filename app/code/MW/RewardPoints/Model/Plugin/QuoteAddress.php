<?php

namespace MW\RewardPoints\Model\Plugin;

class QuoteAddress
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
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return mixed
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        /** @var $orderItem \Magento\Quote\Model\Quote\Address */
        $orderAddress = $proceed($address);

        if ($this->_dataHelper->moduleEnabled()) {
            $orderAddress->setQuoteAddressId($address->getAddressId());
        }

        return $orderAddress;
    }
}
