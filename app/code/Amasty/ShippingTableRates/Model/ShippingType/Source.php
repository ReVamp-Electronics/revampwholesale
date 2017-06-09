<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\ShippingType;

class Source extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = parent::getAllOptions();
            $this->_options[0]['value'] = 0;
            $this->_options[0]['label'] = __('None');
        }

        return $this->_options;
    }
}
