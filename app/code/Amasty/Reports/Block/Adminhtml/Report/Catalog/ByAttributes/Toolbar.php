<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report\Catalog\ByAttributes;

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
        $this->addAttributes($form);

        return parent::addControls($form);
    }
    
    protected function addAttributes($form)
    {
        $this->eavCollection->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, 4);
        $attrAll = $this->eavCollection->load()->getItems();
        $outputAttributes = [];
        $outputAttributes[] = ['label'=> 'Choose Attribute', 'value' => 'NULL'];
        foreach ($attrAll as $item) {
            if ($item->getFrontendInput() == 'multiselect' || $item->getFrontendInput() == 'select') {
                $outputAttributes[] = ['label' => $item->getFrontendLabel(), 'value' => $item->getAttributeId()];
            }
        }
        
        $form->addField('eav', 'select', [
            'name'      => 'eav',
            'values'    => $outputAttributes,
            'no_span'   => true
        ]);

        $form->addField('value', 'radios', [
            'name'      => 'value',
            'values'    => [
                ['value' => 'quantity', 'label' => __('Quantity')],
                ['value' => 'total', 'label' => __('Total')]
            ],
            'value'     => 'quantity'
        ]);

    }
}



