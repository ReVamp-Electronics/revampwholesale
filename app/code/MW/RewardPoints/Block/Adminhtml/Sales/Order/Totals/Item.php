<?php

namespace MW\RewardPoints\Block\Adminhtml\Sales\Order\Totals;

class Item extends \Magento\Sales\Block\Adminhtml\Order\Totals\Item
{
	/**
	 * @var \MW\RewardPoints\Model\RewardpointsorderFactory
	 */
	protected $_rwpOrderFactory;

	/**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
    	$this->_rwpOrderFactory = $rwpOrderFactory;
    }

    /**
     * @return \MW\RewardPoints\Model\Rewardpointsorder
     */
    public function getRewardOrder()
    {
    	return $this->_rwpOrderFactory->create();
    }
}
