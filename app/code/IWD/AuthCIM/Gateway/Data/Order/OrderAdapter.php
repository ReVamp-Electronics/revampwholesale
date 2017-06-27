<?php

namespace IWD\AuthCIM\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\Order\OrderAdapter as MagentoOrderAdapter;
use Magento\Sales\Model\Order;
use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

/**
 * Class OrderAdapter
 * @package IWD\AuthCIM\Gateway\Data\Order
 */
class OrderAdapter extends MagentoOrderAdapter implements OrderAdapterInterface
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var null
     */
    private $billingAddress;

    /**
     * @var null
     */
    private $customerId;

    /**
     * OrderAdapter constructor.
     * @param Order $order
     * @param RegionFactory $regionFactory
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        RegionFactory $regionFactory,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        parent::__construct($order, $addressAdapterFactory);
        $this->order = $order;
        $this->billingAddress = null;
        $this->customerId = null;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Returns tax amount
     *
     * @return int|null
     */
    public function getTaxAmount()
    {
        return $this->order->getTaxAmount();
    }

    /**
     * Returns shipping amount
     *
     * @return int|null
     */
    public function getShippingAmount()
    {
        return $this->order->getShippingAmount();
    }

    /**
     * Returns shipping description
     *
     * @return int|null
     */
    public function getShippingDescription()
    {
        return $this->order->getShippingDescription();
    }

    /**
     * @return string
     */
    public function getBillingAddressArray()
    {
        $billingAddress = parent::getBillingAddress();
        $regionCode = $billingAddress->getRegionCode();
        $countryId = $billingAddress->getCountryId();

        return [
            'firstname' => $billingAddress->getFirstname(),
            'middlename' => $billingAddress->getMiddlename(),
            'lastname' => $billingAddress->getLastname(),
            'email' => $billingAddress->getEmail(),
            'street_line_1' => $billingAddress->getStreetLine1(),
            'street_line_2' => $billingAddress->getStreetLine2(),
            'city' => $billingAddress->getCity(),
            'country_id' => $countryId,
            'region_code' => $regionCode,
            'region_id' => $this->getRegionId($regionCode, $countryId),
            'postcode' => $billingAddress->getPostcode(),
            'company' => $billingAddress->getCompany(),
            'telephone' => $billingAddress->getTelephone()
        ];
    }

    /**
     * @param \IWD\AuthCIM\Gateway\Data\Order\AddressAdapter $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @param $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        if ($this->customerId == null) {
            $this->customerId = parent::getCustomerId();
        }

        return $this->customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        if ($this->billingAddress == null) {
            $this->billingAddress = parent::getBillingAddress();
        }

        return $this->billingAddress;
    }

    /**
     * @param $region
     * @param $countryId
     * @return int
     */
    private function getRegionId($region, $countryId)
    {
        try {
            $regionObject = $this->regionFactory->create()->loadByCode($region, $countryId);
            if ($regionObject) {
                return $regionObject->getRegionId();
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }
}
