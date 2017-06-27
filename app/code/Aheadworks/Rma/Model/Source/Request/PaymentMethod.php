<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\Request;

class PaymentMethod implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var null|array
     */
    protected $options = null;

    /**
     * @var null|array
     */
    protected $optionArray = null;

    /**
     * Payment Helper
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(\Magento\Payment\Helper\Data $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = $this->paymentHelper->getPaymentMethodList();
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $this->optionArray = [];
            foreach ($this->getOptions() as $value => $label) {
                $this->optionArray[] = ['value' => $value, 'label' => $label];
            }
        }
        return $this->optionArray;
    }

    /**
     * @param int $value
     * @return null|array
     */
    public function getOptionLabelByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }

}
