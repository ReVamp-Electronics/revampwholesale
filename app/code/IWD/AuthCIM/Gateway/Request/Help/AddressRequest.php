<?php

namespace IWD\AuthCIM\Gateway\Request\Help;

/**
 * Class AddressRequest
 * @package IWD\AuthCIM\Gateway\Request\Help
 */
class AddressRequest
{
    const SHIPPING_ADDRESS = 'shipTo';
    const BILLING_ADDRESS = 'billTo';
    const FIRST_NAME = 'firstName';
    const LAST_NAME = 'lastName';
    const COMPANY = 'company';
    const STREET_ADDRESS = 'address';
    const LOCALITY = 'city';
    const REGION = 'state';
    const POSTAL_CODE = 'zip';
    const COUNTRY_CODE = 'country';
    const PHONE_NUMBER = 'phoneNumber';
    const FAX_NUMBER = 'faxNumber';

    /**
     * @param $address \Magento\Payment\Gateway\Data\AddressAdapterInterface
     * @return array
     */
    public function getAddressArray($address)
    {
        if ($address == null) {
            return null;
        }

        $street = $address->getStreetLine1() . ' ' . $address->getStreetLine2();
        return [
            self::FIRST_NAME => substr($address->getFirstname(), 0, 50),
            self::LAST_NAME => substr($address->getLastname(), 0, 50),
            self::COMPANY => substr($address->getCompany(), 0, 50),
            self::STREET_ADDRESS => substr($street, 0, 60),
            self::LOCALITY => substr($address->getCity(), 0, 40),
            self::REGION => substr($address->getRegionCode(), 0, 40),
            self::POSTAL_CODE => substr($address->getPostcode(), 0, 20),
            self::COUNTRY_CODE => substr($address->getCountryId(), 0, 60),
            self::PHONE_NUMBER => substr($address->getTelephone(), 0, 60),
            self::FAX_NUMBER => null,
        ];
    }
}
