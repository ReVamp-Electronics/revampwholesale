<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Point extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
	 * @var \MW\RewardPoints\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\RewardPoints\Model\CustomerFactory $customerFactory
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\MW\RewardPoints\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
        parent::__construct($context, $data);
        $this->_customerFactory = $customerFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
    	if (empty($row['entity_id'])) {
    		return '0';
    	}
    	$point = (int) $this->_customerFactory->create()->load($row['entity_id'])->getMwRewardPoint();

    	if ($point == 0) {
    		$point = '0';
    	}

    	return $point;
    }
}
