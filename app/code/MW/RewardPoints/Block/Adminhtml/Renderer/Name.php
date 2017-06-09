<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
        parent::__construct($context, $data);
        $this->_customerFactory = $customerFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
    	if (empty($row['customer_id'])) {
    		return '';
    	}

    	return $this->_customerFactory->create()->load($row['customer_id'])->getName();
    }
}
