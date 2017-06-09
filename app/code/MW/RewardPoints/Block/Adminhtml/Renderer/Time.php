<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

use MW\RewardPoints\Model\Status;

class Time extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

	/**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\Type $type
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Context $context,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\MW\RewardPoints\Model\Type $type,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		array $data = []
	) {
        parent::__construct($context, $data);
        $this->_historyFactory = $historyFactory;
        $this->_type = $type;
        $this->_localeDate = $localeDate;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
    	if (empty($row['history_id'])) {
    		return '';
    	}

    	$miniResult = '';
    	$transaction = $this->_historyFactory->create()->load($row['history_id']);
		$status = $transaction->getStatus();
		$remainingPoint = $transaction->getPointRemaining();
    	$transactionType = $transaction->getTypeOfTransaction();
    	$addPointArray = $this->_type->getAddPointArray();
		$result = $this->_localeDate->formatDateTime(
            new \DateTime($transaction->getTransactionTime()),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
		$expiredTime = $this->_localeDate->formatDateTime(
            new \DateTime($transaction->getExpiredTime()),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );

    	if (in_array($transactionType, $addPointArray)
    		&& $remainingPoint > 0
    		&& $status == Status::COMPLETE
    	) {
    		$miniResult = __('Expires on %1', $expiredTime);
    	}

    	if ($miniResult != '') {
    		$result = $result.'<br><span style="font-size: 11px; color:#808080; font-weight: bold;">'.$miniResult.'</span>';
    	}

		return $result;
    }
}
