<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Model;

class Relations extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * Entity code.
     */
    const ENTITY = 'currencyswitcher_relations';
    
    /**#@+
    * Entity fields.
    */
    const KEY_RELATION_ID = 'relation_id';
    const KEY_CURRENCY_CODE = 'currency_code';
    const KEY_COUNTRIES = 'countries';
    /**#@-*/
    
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\CurrencySwitcher\Model\ResourceModel\Relations');
    }
    
    /**
     * Gets currency code from custom currency relations table
     *
     * @param string $countryCode
     * @return bool|string
     */
    public function getCountryCurrency($countryCode)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where(self::KEY_COUNTRIES . ' LIKE "%' . $countryCode . '%"');

        $relation = $collection->getFirstItem();
        if (!$relation) {
            return false;
        }

        return $relation->getCurrencyCode();
    }
    
    /**
     * Refresh currencyswitcher_relations table
     *
     * @return void
     */
    public function refreshRelations()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stores = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStores(true);
        $helperCurrency = $objectManager->get('\MageWorx\CurrencySwitcher\Helper\Currency');
        $collection     = $this->getCollection();
        $currentCodes   = array();
        $availableCodes = array();

        foreach ($stores as $store) {
            foreach ($store->getAvailableCurrencyCodes(true) as $code) {
                if (!in_array($code, $availableCodes)) {
                    $availableCodes[] = $code;
                }
            }
        }

        foreach ($collection->getItems() as $item) {
            if (!in_array($item->getCurrencyCode(), $availableCodes)) {
                $item->delete();
                continue;
            }
            $currentCodes[] = $item->getCurrencyCode();
        }

        foreach ($availableCodes as $code) {
            if (!in_array($code, $currentCodes)) {
                $data = array(
                    'currency_code' => $code,
                    'countries' => $helperCurrency->getCountryByCurrency($code)
                );
                $this->setData($data)->save();
            }
        }
    }
}
