<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Amount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \MW\RewardPoints\Model\Type
	 */
	protected $_type;

	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
	 * @param \MW\RewardPoints\Model\Type $type
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\MW\RewardPoints\Model\Type $type,
		array $data = []
	) {
        parent::__construct($context, $data);
        $this->_historyFactory = $historyFactory;
        $this->_type = $type;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
    	if (empty($row['history_id'])) {
    		return '';
    	}

    	$history = $this->_historyFactory->create()->load($row['history_id']);
    	$result = $this->_type->getAmountWithSign(
    		$history->getAmount(),
    		$history->getTypeOfTransaction()
    	);

    	return $result;
    }
}
