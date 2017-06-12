<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\StoreSwitcher\Helper;

/**
 * Store Switcher Country helper
 */
class Country extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Convert country code to upper case
     *
     * @param string $countryCode
     * @return string
     */
    public function prepareCode($countryCode)
    {
        return strtoupper(trim($countryCode));
    }

    /**
     * Convert country codes from sql database to simple array
     *
     * @param string $countryCode
     * @return mixed
     */
    public function prepareCountryCode($countryCode)
    {
        if (is_array($countryCode)) {
            $countryCode = current($countryCode);
        }

        $data = @unserialize($countryCode);
        if ($data == false) {
            return $countryCode;
        }
        return $data;
    }
}
