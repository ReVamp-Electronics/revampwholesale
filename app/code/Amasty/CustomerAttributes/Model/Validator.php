<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Validator
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->registry = $registry;
    }

    public function aroundIsValid(
        $subject,
        \Closure $proceed,
        $value
    ) {
        /*
        * fix for situation, when attribute is required and was hided on account edit page - magento can't validate customer.
        */
        if ($value instanceof \Magento\Customer\Model\Customer) {
            $post = $this->request->getServer('REQUEST_URI');
            if ($this->registry->registry('amasty_customerAttributes_validation-failed')) {
                return false;
            }
            if ($post == '/customer/account/editPost/'
                || $post == '/customer/account/createpost/') {
                /*$customerForm = $this->formFactory->create('customer', 'customer_account_edit');
                $customerData = $customerForm->extractData($this->request);
                $allowedAttributes = $customerForm->getAllowedAttributes();
                if ( count($customerData) == count($allowedAttributes) ) {*/
                    return true;
               // }
            }
        }
        $result = $proceed($value);
        return $result;
    }
}
