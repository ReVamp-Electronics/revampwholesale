<?php

namespace MW\RewardPoints\Block\Adminhtml\System\Config;

use MW\RewardPoints\Model\System\Config\Source\Redeemtax;

class Caltax extends \Magento\Config\Block\System\Config\Form\Field
{
	protected function _getElementHtml(
		\Magento\Framework\Data\Form\Element\AbstractElement $element
	) {
		$this->setElement($element);
        $html = '<select id="'.$element->getHtmlId().'" name="'.$element->getName().'" class="select">';

        foreach(Redeemtax::toOptionArray() as $value => $optionName) {
            $html .= '<option value="'.$value.'" '.($element->getValue() == $value ? 'selected="selected"' : '').'>'.$optionName.'</option>';
        }
        $html .= '</select>';

        return $html."
            <script>
                var mwCaltax_config = {
                    element: '".$element->getHtmlId()."',
                    BEFORE_VALUE: '".Redeemtax::BEFORE."',
                    AFTER_VALUE: '".Redeemtax::AFTER."'
                };
            </script>
        ";
	}
}
