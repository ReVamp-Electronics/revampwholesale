<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Cart;

class ShippingMethodConverter
{
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result)
    {
        if ($result->getCarrierCode() == 'amstrates') {
            $methodId = str_replace('amstrates', '', $result->getMethodCode());
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            /**
             * @var \Amasty\ShippingTableRates\Model\Method $method
             */
            $method = $om->get('Amasty\ShippingTableRates\Model\Method')->load($methodId);
            if ($comment = $method->getComment()) {
                $result->setComment($comment);
            }
        }

        return $result;
    }
}
