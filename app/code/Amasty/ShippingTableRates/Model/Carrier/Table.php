<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Table extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'amstrates';
    protected $_isFixed = true;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();

        /* @var \Amasty\ShippingTableRates\Model\Method $modelMethod */
        $modelMethod = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method');
        $collection = $modelMethod->getCollection();
        $collection
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($request->getStoreId())
            ->addCustomerGroupFilter($this->getCustomerGroupId($request));

        /* @var \Amasty\ShippingTableRates\Model\Rate $modelRate */
        $modelRate = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Rate');
        $rates = $modelRate->findBy($request, $collection);

        $countOfRates = 0;
        foreach ($collection as $customMethod) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            // record carrier information
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            if (isset($rates[$customMethod->getId()]['cost'])) {
                // record method information
                $method->setMethod($this->_code . $customMethod->getId());
                $methodTitle = __($customMethod->getName());
                $methodTitle = str_replace('{day}', $rates[$customMethod->getId()]['time'], $methodTitle);
                $method->setMethodTitle($methodTitle);

                $method->setCost($rates[$customMethod->getId()]['cost']);
                $method->setPrice($rates[$customMethod->getId()]['cost']);

                $method->setPos($customMethod->getPos());

                // add this rate to the result
                $result->append($method);
                $countOfRates++;
            }

        }

        if (($countOfRates == 0) && ($this->getConfigData('showmethod') == 1)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        /* @var \Amasty\ShippingTableRates\Model\Method $modelMethod */
        $modelMethod = $this->_objectManager->create('Amasty\ShippingTableRates\Model\Method');
        $collection = $modelMethod->getCollection();
        $collection
            ->addFieldToFilter('is_active', 1);
        $arr = [];
        foreach ($collection as $method) {
            $methodCode = 'amstrates' . $method->getId();
            $arr[$methodCode] = $method->getName();
        }

        return $arr;
    }

    public function getCustomerGroupId($request)
    {
        $allItems = $request->getAllItems();
        if (!$allItems) {
            return 0;
        }
        foreach ($allItems as $item) {
            return $item->getProduct()->getCustomerGroupId();
        }

    }
}
