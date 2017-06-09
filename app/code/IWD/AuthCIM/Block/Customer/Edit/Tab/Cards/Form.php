<?php

namespace IWD\AuthCIM\Block\Customer\Edit\Tab\Cards;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use IWD\AuthCIM\Model\CardRepository;
use IWD\AuthCIM\Model\Customer\Cards as CustomerCards;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Source\Cctype;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Form
 * @package IWD\AuthCIM\Block\Customer\Edit\Tab\Cards
 */
class Form extends Template
{
    /**
     * @var Config
     */
    private $paymentConfig;

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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var Mapper
     */
    private $addressMapper;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerCards
     */
    private $customerCards;

    /**
     * Form constructor.
     * @param Context $context
     * @param Config $paymentConfig
     * @param GatewayConfig $gatewayConfig
     * @param Cctype $ccType
     * @param CardRepository $cardRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Directory\Model\Config\Source\Country $countryFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param AddressConfig $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $paymentConfig,
        GatewayConfig $gatewayConfig,
        Cctype $ccType,
        CardRepository $cardRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        AddressConfig $addressConfig,
        Mapper $addressMapper,
        CustomerRepositoryInterface $customerRepository,
        CustomerCards $customerCards,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->paymentConfig = $paymentConfig;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;
        $this->cardRepository = $cardRepository;
        $this->coreRegistry = $registry;
        $this->countryFactory = $countryFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->customerRepository = $customerRepository;
        $this->customerCards = $customerCards;
    }

    /**
     * @return mixed
     */
    public function getCountries()
    {
        return $this->countryFactory->toOptionArray();
    }

    /**
     * @return string
     */
    public function getCustomerProfileId()
    {
        $customerId = $this->getCustomerId();
        return $this->customerCards->getCustomerProfileId($customerId);
    }

    /**
     * @return array
     */
    public function getCountryRegions()
    {
        $countryRegions = [];

        $regionsCollection = $this->regionCollectionFactory->create();
        foreach ($regionsCollection as $region) {
            $countryRegions[$region->getCountryId()][$region->getId()] = $region->getDefaultName();
        }

        return $countryRegions;
    }

    /**
     * @return bool
     */
    public function hasVerification()
    {
        return $this->gatewayConfig->getRequireCvv();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if ($months === null) {
            $months = $this->paymentConfig->getMonths();
            $months = [0 => __('Month')] + $months;
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if ($years === null) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();
        $availableTypes = $this->gatewayConfig->getAvailableCardTypes();
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    /**
     * @return string
     */
    public function getMethodCode()
    {
        return \IWD\AuthCIM\Model\Ui\ConfigProvider::CODE;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return string
     */
    public function getJsConfig()
    {
        $data = [
            'deleteUrl' => $this->getUrl('iwd_authcim/customer_cards/delete', ['_current' => true]),
            'addUrl' => $this->getUrl('iwd_authcim/customer_cards/add', ['_current' => true]),
            'updateUrl' => $this->getUrl('iwd_authcim/customer_cards/update', ['_current' => true]),
            'syncUrl' => $this->getUrl('iwd_authcim/customer_cards/sync', ['_current' => true]),
            'statusUrl' => $this->getUrl('iwd_authcim/customer_cards/status', ['_current' => true]),
            'acceptEnabled' => $this->isAcceptJsEnabled()
        ];

        if ($this->isAcceptJsEnabled()) {
            $data['apiLoginId'] = $this->getApiLoginId();
            $data['acceptKey'] = $this->getAcceptJsKey();
        }

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getCardsList()
    {
        $cards = $this->getSavedCcList();
        $cardList = [];

        foreach ($cards as $card) {
            $cardList[$card->getHash()] = [
                'hash' => $card->getHash(),
                'payment_id' => $card->getPaymentId(),
                'address' => unserialize($card->getAddress()),
                'payment' => [
                    'cc_exp_month' => $card->getExpirationMonth(),
                    'cc_exp_year' => $card->getExpirationYear(),
                    'cc_type' => $card->getCreditCardTypeCode()
                ]
            ];
        }

        return json_encode($cardList);
    }

    /**
     * @return CardRepository
     */
    public function getCardRepository()
    {
        return $this->cardRepository;
    }

    /**
     * @return \IWD\AuthCIM\Api\Data\CardInterface[]
     */
    public function getSavedCcList()
    {
        $customerId = $this->getCustomerId();
        return $this->getCardRepository()->getListForCustomer($customerId, null)->getItems();
    }

    /**
     * @param $card \IWD\AuthCIM\Api\Data\CardInterface
     * @return string
     */
    public function getAddressHtml($card)
    {
        $address = $card->getAddress();
        $address = !empty($address) ? unserialize($address) : [];
        $address['street'] = isset($address['street_line_1'])
            ? $address['street_line_1'] . (isset($address['street_line_2']) ? ' ' . $address['street_line_2'] : '')
            : '';
        $address['region'] = isset($address['region_code']) ? $address['region_code'] : '';

        try {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($address);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $card \IWD\AuthCIM\Api\Data\CardInterface
     * @return bool
     */
    public function isAllowedCcType($card)
    {
        $type = $card->getAdditionalData('cc_type');

        $availableTypes = $this->gatewayConfig->getAvailableCardTypes();
        if (in_array($type, $availableTypes)) {
            return true;
        }

        $types = $this->paymentConfig->getCcTypes();
        $type = array_search($type, $types);
        if ($type !== null) {
            return in_array($type, $availableTypes);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAcceptJsEnabled()
    {
        return $this->gatewayConfig->isAcceptJsEnabled() ? 1 : 0;
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
