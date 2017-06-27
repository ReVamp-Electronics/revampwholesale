<?php

namespace IWD\SalesRep\Helper\Plugin\B2B;

/**
 * Class B2BHelperTrait
 * @package IWD\SalesRep\Helper\Plugin\B2B
 */
trait B2BHelperTrait
{
    protected $_hasParentAccount = null;

    protected $_b2bCustomer;

    protected $_salesrepHelper;

    private $objectManager;

    function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \IWD\SalesRep\Helper\Data $salesrepHelper
    ) {
        $this->objectManager = $objectManager;
        $this->_salesrepHelper = $salesrepHelper;
        if ($this->isB2BInstalled()) {
            $this->_b2bCustomer = $this->objectManager->create("\IWD\B2B\Model\CustomerFactory");
        }
    }

    /**
     * @return bool
     */
    public function isSalesrepLoggedInAsCustomer()
    {
        if ($this->_hasParentAccount === null) {
            try {
                $this->_hasParentAccount = (bool)$this->_salesrepHelper->getParentAccount();
            } catch (\Exception $e) {
                $this->_hasParentAccount = false;
            }
        }
        return $this->_hasParentAccount;
    }

    public function isB2BInstalled()
    {
        return class_exists('\IWD\B2B\Helper\Data');
    }
}
