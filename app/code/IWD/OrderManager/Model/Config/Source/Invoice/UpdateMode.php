<?php

namespace IWD\OrderManager\Model\Config\Source\Invoice;

use \Magento\Framework\Option\ArrayInterface;

/**
 * Class UpdateMode
 * @package IWD\OrderManager\Model\Config\Source\Invoice
 */
class UpdateMode implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'add',
                'label' => __('Create new invoice (if possible)')
            ], [
                'value' => 'rebuild',
                'label' => __('Delete invoices and create new')
            ],
        ];
    }
}
