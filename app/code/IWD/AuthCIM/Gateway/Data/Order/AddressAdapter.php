<?php

namespace IWD\AuthCIM\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Framework\DataObject;
use Magento\Directory\Model\RegionFactory;

/**
 * Class AddressAdapter
 */
class AddressAdapter extends DataObject implements AddressAdapterInterface
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    public function __construct(RegionFactory $regionFactory, array $data = [])
    {
        parent::__construct($data);
        $this->regionFactory = $regionFactory;
    }

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegionCode()
    {
        $regionId = (!$this->getRegionId() && is_numeric($this->getRegion())) ?
            $this->getRegion() :
            $this->getRegionId();
        $model = $this->regionFactory->create()->load($regionId);
        if ($model->getCountryId() == $this->getCountryId()) {
            return $model->getCode();
        } elseif (is_string($this->getRegion())) {
            return $this->getRegion();
        } else {
            return null;
        }
    }

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData('region');
    }

    /**
     * Get region id
     *
     * @return string
     */
    public function getRegionId()
    {
        return $this->getData('region_id');
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData('country_id');
    }

    /**
     * Get street line 1
     *
     * @return string
     */
    public function getStreetLine1()
    {
        return $this->getData('street_line_1');
    }

    /**
     * Get street line 2
     *
     * @return string
     */
    public function getStreetLine2()
    {
        return $this->getData('street_line_2');
    }

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData('telephone');
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData('postcode');
    }

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData('city');
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData('firstname');
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getData('lastname');
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->getData('middlename');
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * Returns name prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getData('prefix');
    }

    /**
     * Returns name suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->getData('suffix');
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getData('company');
    }
}
