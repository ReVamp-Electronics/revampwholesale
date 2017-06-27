<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\Data\SourceAddressInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class SourceAddress
 * @package IWD\MultiInventory\Model\Warehouses
 */
class SourceAddress extends AbstractExtensibleModel implements SourceAddressInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('IWD\MultiInventory\Model\ResourceModel\Warehouses\SourceAddress');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(self::STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->_getData(self::STREET);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->_getData(self::REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->_getData(self::REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->_getData(self::POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->setData(self::STOCK_ID, $stockId);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setStreet($street)
    {
        $this->setData(self::STREET, $street);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        $this->setData(self::CITY, $city);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setRegionId($regionId)
    {
        $this->setData(self::REGION_ID, $regionId);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        $this->setData(self::REGION, $region);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        $this->setData(self::POSTCODE, $postcode);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setCountryId($countryId)
    {
        $this->setData(self::COUNTRY_ID, $countryId);
        return $this;
    }
}
