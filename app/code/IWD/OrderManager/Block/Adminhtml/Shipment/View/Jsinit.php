<?php

namespace IWD\OrderManager\Block\Adminhtml\Shipment\View;

use IWD\OrderManager\Block\Adminhtml\Order\View\Jsinit as OrderJsinit;

/**
 * Class Jsinit
 * @package IWD\OrderManager\Block\Adminhtml\Shipment\View
 */
class Jsinit extends OrderJsinit
{
    /**
     * @inheritdoc
     */
    protected function checkIsEditAllowed()
    {
        $this->disallowed = [];
    }

    /**
     * @return string
     */
    public function jsonParamsShipmentInfo()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/shipment_info/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/shipment_info/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }
}
