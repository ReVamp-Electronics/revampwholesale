<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Helper;


use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Session\Storage;
use Magento\Checkout\Model\Session as CheckoutSession;

class Session extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Storage
     */
    protected $session;

    public function __construct(
        Context $context,
        Storage $sessionStorage
    )
    {
        $this->session = $sessionStorage;
        parent::__construct($context);
    }

    public function setCustomerAttributesToSession($customerAttributes)
    {
        $this->session->setData(
            'amasty_customer_attributes_quote', $customerAttributes
        );
    }

    public function getCustomerAttributesFromSession()
    {
        $customerAttributesRow = $this->session->getData(
            'amasty_customer_attributes_quote'
        );
        if (!$customerAttributesRow) {
            $customerAttributesRow = [];
        }

        $customerAttributes = [];
        foreach ($customerAttributesRow as $customerAttribute) {
            $customerAttributes[$customerAttribute->getAttributeCode()] = $customerAttribute->getValue();
        }

        return $customerAttributes;
    }
}
