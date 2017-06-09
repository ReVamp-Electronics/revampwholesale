<?php

namespace IWD\MultiInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SourceAddressInterface
 * @package IWD\MultiInventory\Api\Data
 * @api
 */
interface SourceAddressInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const STOCK_ID = 'stock_id';
    const STREET = 'street';
    const CITY = 'city';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const POSTCODE = 'postcode';
    const COUNTRY_ID = 'country_id';
    /**#@-*/

    /**
     * Retrieve id
     *
     * @return int
     */
    public function getId();

    /**
     * Retrieve stock id
     *
     * @return int
     */
    public function getStockId();

    /**
     * Retrieve street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Retrieve city
     *
     * @return string
     */
    public function getCity();

    /**
     * Retrieve region id
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Retrieve region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Retrieve postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Retrieve country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set stock id
     *
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId);

    /**
     * Set street
     *
     * @param string $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);
}
