<?php

namespace MW\RewardPoints\Block\Adminhtml\System\Config;

class Redship extends \Magento\Config\Block\System\Config\Form\Field
{
	protected function _getElementHtml(
		\Magento\Framework\Data\Form\Element\AbstractElement $element
	) {
		$this->setElement($element);

        return parent::_getElementHtml($element)."
        	<script>
        		require([
        			'mwHeadMain'
        		], function() {
        			var mwRWPCaltax = new MW.RewardPoint.SystemConfig.Caltax(mwCaltax_config);
        		});
			</script>
        ";
	}
}
