<?php

namespace IWD\SalesRep\Block\B2B;

use Magento\Framework\Registry;

/**
 * Class CustomersList
 * @package IWD\SalesRep\Block\B2B
 */
class CustomersList extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'b2b/customers/list.phtml';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var
     */
    private $customersList;

    /**
     * CustomersList constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCustomersList()
    {
        if ($this->customersList === null) {
            $this->customersList = $this->registry->registry('customers_list');
        }
        return $this->customersList;
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
