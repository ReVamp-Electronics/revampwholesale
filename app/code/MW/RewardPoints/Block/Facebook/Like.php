<?php

namespace MW\RewardPoints\Block\Facebook;

class Like extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Framework\UrlInterface
	 */
	protected $_urlBuilder;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * @var \Magento\Cms\Model\Page
	 */
	protected $_cmsPage;

	/**
	 * @var \MW\RewardPoints\Helper\Data
	 */
	protected $_dataHelper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Framework\UrlInterface $urlBuilder
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Cms\Model\Page $cmsPage
	 * @param \MW\RewardPoints\Helper\Data $dataHelper
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\UrlInterface $urlBuilder,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Cms\Model\Page $cmsPage,
		\MW\RewardPoints\Helper\Data $dataHelper,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_urlBuilder = $urlBuilder;
		$this->_coreRegistry = $coreRegistry;
		$this->_cmsPage = $cmsPage;
		$this->_dataHelper = $dataHelper;
	}

	/**
	 * Get current URL
	 *
	 * @return string
	 */
	public function getCurrentUrl()
    {
    	return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get site name
     *
     * @return string
     */
    public function getSiteName()
    {
    	return $this->_dataHelper->getStoreConfig('general/store_information/name');
    }

    /**
     * Get Facebook App ID
     *
     * @return string
     */
    public function getAppId()
    {
    	return $this->_dataHelper->getFacebookLikeAppId();
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
    	return $this->_coreRegistry;
    }

    /**
     * @return \Magento\Cms\Model\Page
     */
    public function getCmsPage()
    {
    	return $this->_cmsPage;
    }
}
