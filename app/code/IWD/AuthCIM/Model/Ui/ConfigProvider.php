<?php

namespace IWD\AuthCIM\Model\Ui;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Model\CardRepository;
use IWD\AuthCIM\Model\Customer\Cards as CustomerCards;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'iwd_authcim';

    /**
     * @var GatewayConfig
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CardRepository
     */
    private $cardRepository;

    /**
     * @var CustomerCards
     */
    private $customerCards;

    /**
     * ConfigProvider constructor.
     * @param GatewayConfig $gatewayConfig
     * @param CheckoutSession $checkoutSession
     * @param CardRepository $cardRepository
     * @param CustomerCards $customerCards
     */
    public function __construct(
        GatewayConfig $gatewayConfig,
        CheckoutSession $checkoutSession,
        CardRepository $cardRepository,
        CustomerCards $customerCards
    ) {
        $this->config = $gatewayConfig;
        $this->checkoutSession = $checkoutSession;
        $this->cardRepository = $cardRepository;
        $this->customerCards = $customerCards;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $isAcceptjsEnabled = $this->config->isAcceptJsEnabled();

        $config = [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->config->isActive(),
                    'availableCardTypes' => $this->config->getAvailableCardTypes(),
                    'useCvv' => $this->config->isCvvEnabled(),
                    'isSaveCc' => $this->isSaveCc(),
                    'isGuestCheckout' => $this->isGuestCheckout(),
                    'savedCcList' => $this->getSavedCreditCardForCustomer(),
                    'isAcceptjsEnabled' => $isAcceptjsEnabled
                ]
            ]
        ];

        if ($isAcceptjsEnabled) {
            $config['payment'][self::CODE]['apiLoginID'] = $this->config->getApiLoginId();
            $config['payment'][self::CODE]['clientKey'] = $this->config->getAcceptJsKey();
            $config['payment'][self::CODE]['sdkAcceptJsUrl'] = $this->config->getAcceptJsUrl();
        }

        return $config;
    }

    /**
     * @return bool
     */
    private function isSaveCc()
    {
        return $this->config->isSaveCreditCard();
    }

    /**
     * @return array
     */
    private function getSavedCreditCardForCustomer()
    {
        if ($this->isGuestCheckout()) {
            return [];
        }

        $customerId = $this->getCustomerId();
        $customerProfileId = $this->customerCards->getCustomerProfileId($customerId);
        $this->customerCards->syncCustomerProfile($customerProfileId, $customerId);

        return $this->cardRepository->getSavedCcListForCustomer($customerId);
    }

    /**
     * @return bool
     */
    private function isGuestCheckout()
    {
        return $this->getCustomerId() == 0;
    }

    /**
     * @return int
     */
    private function getCustomerId()
    {
        return $this->checkoutSession->getQuote()->getCustomerId();
    }
}
