<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer\Config;

class Bannersize extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(
		\Magento\Framework\Data\Form\Element\AbstractElement $element
	) {
        $element->setStyle('width:70px;')->setName($element->getName().'[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = [];
        }

        $from = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $to   = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();

        return $from . ' x ' . $to . 'px';
    }
}
