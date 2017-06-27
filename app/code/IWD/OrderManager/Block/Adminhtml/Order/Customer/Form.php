<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Customer;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;
use Magento\Customer\Model\Group;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Customer
 */
class Form extends AbstractForm
{
    /**
     * @var []
     */
    private $options = [];

    /**
     * @var \IWD\OrderManager\Model\Order\Order
     */
    private $order;

    /**
     * @var \Magento\Customer\Model\Group
     */
    private $customerGroup;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Group $group
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Group $group,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerGroup = $group;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \IWD\OrderManager\Model\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderCustomerParams()
    {
        $this->options = [];

        $this->addCustomerEmail();

        $this->addCustomerGroups();

        $this->addPrefix();
        $this->addFirstName();
        $this->addMiddleName();
        $this->addLastnameName();
        $this->addSuffix();

        $this->addGender();
        $this->addTaxvat();

        $this->addDateOfBirth();
        $this->addCustomerId();

        return $this->options;
    }

    /**
     * @return void
     */
    private function addMiddleName()
    {
        $middleNameShow = $this->_scopeConfig->getValue('customer/address/middlename_show');
        if ($middleNameShow) {
            $this->options['customer_middlename'] = [
                'value' => $this->getOrder()->getData('customer_middlename'),
                'title' => 'Middle Name',
                'required' => false
            ];
        }
    }

    /**
     * @return void
     */
    private function addPrefix()
    {
        $prefixShow = $this->_scopeConfig->getValue('customer/address/prefix_show');
        if ($prefixShow) {
            $prefixShow = ($prefixShow == 'opt');
            $prefixOptions = $this->getCustomerPrefixOptions();

            $this->options['customer_prefix'] = [
                'value' => $this->getOrder()->getData('customer_prefix'),
                'title' => 'Prefix',
                'required' => $prefixShow,
                'options' => $prefixOptions
            ];
        }
    }

    /**
     * @return array
     */
    private function getCustomerPrefixOptions()
    {
        $prefixOptions = trim($this->_scopeConfig->getValue('customer/address/prefix_options'));
        $prefixOptions = !empty($prefixOptions) ? explode(';', $prefixOptions) : null;

        if (is_array($prefixOptions) && !empty($prefixOptions)) {
            $prefixOptions = array_combine($prefixOptions, $prefixOptions);
        }

        return $prefixOptions;
    }

    /**
     * @return void
     */
    private function addSuffix()
    {
        $suffixShow = $this->_scopeConfig->getValue('customer/address/suffix_show');
        if ($suffixShow) {
            $suffixShow = ($suffixShow == 'opt');
            $suffixOptions = $this->getCustomerSuffixOptions();
            $this->options['customer_suffix'] = [
                'value' => $this->getOrder()->getData('customer_suffix'),
                'title' => 'Suffix',
                'required' => $suffixShow,
                'options' => $suffixOptions
            ];
        }
    }

    /**
     * @return array
     */
    private function getCustomerSuffixOptions()
    {
        $suffixOptions = trim($this->_scopeConfig->getValue('customer/address/suffix_options'));
        $suffixOptions = !empty($suffixOptions) ? explode(';', $suffixOptions) : null;

        if (is_array($suffixOptions) && !empty($suffixOptions)) {
            $suffixOptions = array_combine($suffixOptions, $suffixOptions);
        }

        return $suffixOptions;
    }

    /**
     * @return void
     */
    private function addCustomerGroups()
    {
        $customerGroups = $this->getCustomerGroups();

        $this->options['customer_group_id'] = [
            'value' => $this->getOrder()->getData('customer_group_id'),
            'title' => 'Customer Group',
            'required' => true,
            'options' => $customerGroups
        ];
    }

    /**
     * @return void
     */
    private function addCustomerEmail()
    {
        $this->options['customer_email'] = [
            'value' => $this->getOrder()->getData('customer_email'),
            'title' => 'Email',
            'required' => true
        ];
    }

    /**
     * @return void
     */
    private function addFirstName()
    {
        $this->options['customer_firstname'] = [
            'value' => $this->getOrder()->getData('customer_firstname'),
            'title' => 'First Name',
            'required' => true
        ];
    }

    /**
     * @return void
     */
    private function addLastnameName()
    {
        $this->options['customer_lastname'] = [
            'value' => $this->getOrder()->getData('customer_lastname'),
            'title' => 'Last Name',
            'required' => true
        ];
    }

    /**
     * @return void
     */
    private function addGender()
    {
        $genderShow = $this->_scopeConfig->getValue('customer/address/gender_show');
        if ($genderShow) {
            $genderShow = ($genderShow == 'opt');
            $this->options['customer_gender'] = [
                'value' => $this->getOrder()->getData('customer_gender'),
                'title' => 'Gender',
                'required' => $genderShow,
                'options' => [''=>'', '1'=>'Male', '2'=>'Female', '3'=>'Not Specified']
            ];
        }
    }

    /**
     * @return void
     */
    private function addTaxvat()
    {
        $taxvatShow = $this->_scopeConfig->getValue('customer/address/taxvat_show');
        if ($taxvatShow) {
            $taxvatShow = ($taxvatShow == 'opt');
            $this->options['customer_taxvat'] = [
                'value' => $this->getOrder()->getData('customer_taxvat'),
                'title' => 'Tax/VAT Number',
                'required' => $taxvatShow
            ];
        }
    }

    /**
     * @return void
     */
    private function addDateOfBirth()
    {
        $dobShow = $this->_scopeConfig->getValue('customer/address/dob_show');
        if ($dobShow) {
            $date = $this->getOrder()->getData('customer_dob');
            $date = !empty($date) ? date('Y-m-d', strtotime($date)) : '';
            $dobShow = ($dobShow == 'opt') ? true : false;
            $this->options['customer_dob'] = [
                'value' => $date,
                'title' => 'Date of Birth',
                'class' => 'datapicker',
                'required' => $dobShow
            ];
        }
    }

    /**
     * @return void
     */
    private function addCustomerId()
    {
        $customerId = $this->getOrder()->getData('customer_id');
        $this->options['customer_id'] = [
            'value' => $customerId
        ];
    }

    /**
     * @return array
     */
    private function getCustomerGroups()
    {
        $groups = $this->customerGroup->getCollection();
        $customerGroups = [];

        /** @var $group \Magento\Customer\Model\Group */
        foreach ($groups as $group) {
            $customerGroups[$group->getId()] = $group->getCustomerGroupCode();
        }

        return $customerGroups;
    }

    /**
     * @return mixed
     */
    public function getAllowChangeState()
    {
        return $this->_scopeConfig->getValue('iwdordermanager/order_info/edit_state');
    }
}
