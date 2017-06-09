<?php

namespace MW\RewardPoints\Block\Adminhtml\System\Config;

class AuthorInformation extends \Magento\Config\Block\System\Config\Form\Field
{
	protected function _getElementHtml(
		\Magento\Framework\Data\Form\Element\AbstractElement $element
	) {
		$html = $this->getLayout()
			->createBlock('Magento\Framework\View\Element\Template')
			->setTemplate('MW_RewardPoints::system/config/author.phtml')
			->toHtml();

        return $html;
    }
}
