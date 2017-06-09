<?php

namespace IWD\OrderManager\Block\Adminhtml\Invoice\View;

use IWD\OrderManager\Block\Adminhtml\Order\View\Jsinit as OrderJsinit;

/**
 * Class Jsinit
 * @package IWD\OrderManager\Block\Adminhtml\Invoice\View
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
    public function jsonParamsInvoiceInfo()
    {
        $data = [
            'urlForm' => $this->_urlBuilder->getUrl('iwdordermanager/invoice_info/form'),
            'urlUpdate' => $this->_urlBuilder->getUrl('iwdordermanager/invoice_info/update'),
            'disallowed' => $this->disallowed
        ];

        return json_encode($data);
    }
}
