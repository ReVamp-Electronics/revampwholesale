<?php

namespace IWD\AuthCIM\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Customer\Controller\RegistryConstants;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;

/**
 * Class Cards
 * @package IWD\AuthCIM\Controller\Customer
 */
class Cards extends Action
{
    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \IWD\AuthCIM\Model\Customer\Cards
     */
    private $customerCards;

    /**
     * @param Context $context
     * @param GatewayConfig $gatewayConfig
     * @param Session $session
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        GatewayConfig $gatewayConfig,
        Session $session,
        Registry $coreRegistry,
        \IWD\AuthCIM\Model\Customer\Cards $customerCards
    ) {
        parent::__construct($context);
        $this->gatewayConfig = $gatewayConfig;
        $this->session = $session;
        $this->coreRegistry = $coreRegistry;
        $this->customerCards = $customerCards;
    }

    /**
     * Authenticate customer
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->session->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->gatewayConfig->isActive()) {
            $this->_redirect('customer/account/');
            return;
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $this->session->getCustomer()->getId());

        $this->syncCustomer();

        $this->_view->loadLayout();
        $this->_view->loadLayoutUpdates();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('My Secure Wallet'));
        $this->_view->renderLayout();
    }

    /**
     * Sync customer profile from Authorize.net and Magento
     */
    private function syncCustomer()
    {
        $customerId = (int)$this->session->getCustomer()->getId();
        $customerProfileId = $this->customerCards->getCustomerProfileId($customerId);
        $this->customerCards->syncCustomerProfile($customerProfileId, $customerId);
    }
}
