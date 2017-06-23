<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Sales\Orders;

use Magento\Framework\Data\Form\AbstractForm;

class Toolbar extends \Amasty\Reports\Block\Adminhtml\Report\Toolbar
{
    /**
     * @param AbstractForm $form
     *
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $this->addDateControls($form);

        $form->addField('type', 'select', [
            'name'      => 'type',
            'values'    => [
                ['value' => 'overview', 'label' => __('Overview')],
                ['value' => 'status', 'label' => __('By Status')]
            ],
            'value'     => 'type'
        ]);

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);
        
        return parent::addControls($form);
    }
}
