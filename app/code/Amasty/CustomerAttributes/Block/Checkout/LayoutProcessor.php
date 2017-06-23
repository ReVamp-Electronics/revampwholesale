<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Block\Checkout;

use Amasty\CustomerAttributes\Component\Form\AttributeMapper;
use Amasty\CustomerAttributes\Component\Form\AttributeMerger;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Session as CustomerSession;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    const BILLING_FILLED = '1';
    const BILLING_NOT_FILLED = '0';
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Amasty\CustomerAttributes\Component\Form\AttributeMapper
     */
    private $attributeMapper;

    /**
     * @var AttributeMerger
     */
    private $merger;

    /**
     * @var array
     */
    private $jsLayout;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Amasty\CustomerAttributes\Component\Form\AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!isset($jsLayout['components']['checkout'])) {
            return $jsLayout;
        }

        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer',
            'customer_attributes_checkout'
        );

        $elements = $this->getElements($attributes);

        if (!$elements) {
            return $jsLayout;
        }
        $customer = $this->customerSession->getCustomer();
        if (!$this->customerSession->isLoggedIn() || !$customer->getDefaultShippingAddress()) {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])
            ) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['customer_attributes_renderer'] =
                    [
                        'component' => "customerAttributesCheckoutGuest"
                    ];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['customer_attributes_renderer']['children'] = $this->merger->merge(
                    $elements,
                    'checkoutProvider',
                    'shippingAddress.custom_attributes',
                    []
                );
            }

        } else {
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']['children'])
            ) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']
                ['children']['customer_attributes_renderer'] =
                    [
                        'component' => "customerAttributesCheckout"
                    ];

                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']
                ['children']['customer_attributes_renderer']['children'] = $this->merger->merge(
                    $elements,
                    'checkoutProvider',
                    'shippingAddress.custom_attributes',
                    []
                );
            }

        }

        return $jsLayout;
    }

    private function getElements($attributes)
    {
        $customerData = [];
        if ($this->customerSession->getCustomerId()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customerData = $customer->getCustomAttributes();
        }
        $elements = [];
        $store = $this->storeManager->getStore()->getId();

        foreach ($attributes as $attribute) {
            $stores = $attribute->getStoreIds();
            $stores = explode(',', $stores);

            $isBilling =
                !(!($attribute->getBillingFilled() == self::BILLING_FILLED
                    && array_key_exists($attribute->getAttributeCode(), $customerData))
                    || $attribute->getBillingFilled() == self::BILLING_NOT_FILLED
                );

            if ($isBilling || !in_array($store, $stores)) {
                continue;
            }
            $key = $attribute->getAttributeCode();
            $elements[$key] = $this->attributeMapper->map($attribute);
            if (isset($elements[$key]['label'])) {
                $label = $elements[$key]['label'];
                $elements[$key]['label'] = __($label);
                if (isset($customerData[$attribute->getAttributeCode()])) {
                    $elements[$key]['value'] = $customerData[$key]->getValue();
                }
            }
        }

        return $elements;
    }
}
