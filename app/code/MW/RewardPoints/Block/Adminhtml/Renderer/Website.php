<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
	 * @var \Magento\Store\Model\WebsiteFactory
	 */
	protected $_websiteFactory;

	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Store\Model\WebsiteFactory $websiteFactory,
		array $data = []
	) {
        parent::__construct($context, $data);
        $this->_websiteFactory = $websiteFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
    	if (sizeof($row->getWebsiteIds()) > 1) {
    		$result = '';
    		$websiteCollection = $this->_websiteFactory->create()->getCollection()
    			->addFieldToFilter('website_id', ['in' => $row->getWebsiteIds()]);

	    	foreach ($websiteCollection as $webste) {
	    		$result .= $webste->getName();
	    	}

	    	return $result;
    	} else {
    		$websiteIds = $row->getWebsiteIds();
    		$website = $this->_websiteFactory->create()->load($websiteIds[0]);

    		return $website->getName();
    	}
    }
}
