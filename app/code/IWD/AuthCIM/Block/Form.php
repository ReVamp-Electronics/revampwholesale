<?php

namespace IWD\AuthCIM\Block;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Model\CardRepository;
use IWD\AuthCIM\Model\Customer\Cards as CustomerCards;
use Magento\Backend\Model\Session\Quote;
use Magento\Payment\Model\Source\Cctype;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;

/**
 * Class Form
 */
class Form extends Cc
{
    protected $_template = 'IWD_AuthCIM::form/cc.phtml';

    /**
     * @var Quote
     */
    private $sessionQuote;

    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @var Cctype
     */
    private $ccType;

    /**
     * @var CardRepository
     */
    private $cardRepository;

    /**
     * @var CustomerCards
     */
    private $customerCards;

    /**
     * Form constructor.
     * @param Context $context
     * @param Config $paymentConfig
     * @param Quote $sessionQuote
     * @param GatewayConfig $gatewayConfig
     * @param Cctype $ccType
     * @param CardRepository $cardRepository
     * @param CustomerCards $customerCards
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        Cctype $ccType,
        CardRepository $cardRepository,
        CustomerCards $customerCards,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;
        $this->cardRepository = $cardRepository;
        $this->customerCards = $customerCards;
    }

    /**
     * @return boolean
     */
    public function useCvv()
    {
        return $this->gatewayConfig->isCvvEnabled();
    }

    /**
     * @return bool
     */
    public function isVaultEnabled()
    {
        return $this->gatewayConfig->isCvvEnabled();
    }

    /**
     * @return bool
     */
    public function isSaveCreditCard()
    {
        return $this->gatewayConfig->isSaveCreditCard();
    }

    /**
     * @return bool
     */
    public function getTitle()
    {
        return $this->gatewayConfig->getTitle();
    }

    /**
     * @return array
     */
    public function getSavedCcList()
    {
        $this->autoSyncSavedCardsWithAuthNet();
        $customerId = $this->getCustomerId();

        return $this->cardRepository->getSavedCcListForCustomer($customerId);
    }

    private function autoSyncSavedCardsWithAuthNet()
    {
        $enabled = true;

        if ($enabled) {
            $customerId = $this->getCustomerId();
            $customerProfileId = $this->customerCards->getCustomerProfileId($customerId);
            $this->customerCards->syncCustomerProfile($customerProfileId, $customerId);
        }
    }

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return $this->sessionQuote->getCustomerId();
    }

    /**
     * @return bool
     */
    public function isAcceptJsEnabled()
    {
        return $this->gatewayConfig->isAcceptJsEnabled();
    }

    /**
     * @return string
     */
    public function getAcceptJsUrl()
    {
        return $this->gatewayConfig->getAcceptJsUrl();
    }

    /**
     * @return string
     */
    public function getAcceptJsKey()
    {
        return $this->gatewayConfig->getAcceptJsKey();
    }

    /**
     * @return string
     */
    public function getApiLoginId()
    {
        return $this->gatewayConfig->getApiLoginId();
    }
}
