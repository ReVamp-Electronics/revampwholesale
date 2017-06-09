<?php

namespace IWD\SalesRep\Block\B2B;

/**
 * Class Footer
 * @package IWD\SalesRep\Block\B2B
 */
class Footer extends  \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    private $salesrepHelper;

    /**
     * Footer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\SalesRep\Helper\Data $salesrepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->salesrepHelper = $salesrepHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'customersListUrl' => $this->getUrl('salesrep/customer/customersList'),
        ];
    }

    /**
     * @return \IWD\SalesRep\Helper\Data
     */
    public function getHelper()
    {
        return $this->salesrepHelper;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return $this|bool|null
     */
    public function getParentAccount()
    {
        try {
            return $this->salesrepHelper->getParentAccount();
        } catch (\IWD\SalesRep\Exceptions\MissingParentAccountException $e) {
            $this->_logger->alert(
                sprintf(
                    'Customer %d has paent account %d, which does not exists',
                    $this->customerSession->getCustomerId(),
                    $this->customerSession->getData(\IWD\SalesRep\Helper\Data::SESSION_VAR_PARENT_ACCOUNT_ID)
                )
            );

            return null;
        }
    }
}
