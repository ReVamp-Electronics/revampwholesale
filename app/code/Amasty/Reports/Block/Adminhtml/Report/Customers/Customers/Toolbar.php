<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Customers\Customers;

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

        $form->addField('interval', 'radios', [
            'name'      => 'interval',
            'values'    => [
                ['value' => 'day', 'label' => __('Day')],
                ['value' => 'week', 'label' => __('Week')],
                ['value' => 'month', 'label' => __('Month')],
                ['value' => 'year', 'label' => __('Year')],
            ],
            'value'     => 'day'
        ]);
        
        return parent::addControls($form);
    }
}
