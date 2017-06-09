<?php

namespace MW\RewardPoints\Block\Rewardpoints;

use MW\RewardPoints\Model\Status;
use MW\RewardPoints\Model\Type;

class Transaction extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
	 */
	protected $_localeDate;

	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \MW\RewardPoints\Model\Type
	 */
	protected $_type;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
	 * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
	 * @param \MW\RewardPoints\Model\Type $type
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\MW\RewardPoints\Model\Type $type,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_customerSession = $customerSession;
		$this->_historyFactory = $historyFactory;
		$this->_type = $type;
		$this->_localeDate = $localeDate;
	}

	/**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
        	'Magento\Theme\Block\Html\Pager',
        	'rewardpoints_transaction_toolbar'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->_getTransaction());

        return $this;
    }

    public function getLimit()
    {
    	$limits = [10 => 10, 20 => 20, 50 => 50];
        if ($limit = $this->getRequest()->getParam('limit')) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }

        return 20;
    }

	protected function _getCustomer()
	{
		return $this->_customerSession->getCustomer();
	}

	public function _getTransaction()
	{
		$transactions = $this->_historyFactory->create()->getCollection()
			->addFieldToFilter('customer_id', $this->_getCustomer()->getId())
			->addFieldToFilter('status', ['in' => [Status::COMPLETE, Status::PENDING]])
			->addOrder('transaction_time','DESC')
			->addOrder('history_id','DESC');

		return $transactions;
	}

	public function getTransaction()
	{
		return $this->getToolbar()->getCollection();
	}

	public function getTransactionDetail($type, $detail = null, $status = null)
	{
		return $this->_type->getTransactionDetail($type, $detail, $status);
	}

	public function getFormatDateNew($transaction)
	{
		$status = $transaction->getStatus();
		$pointRemaining = $transaction->getPointRemaining();
    	$addPointArray = $this->_type->getAddPointArray();
    	$typeOfTransaction = $transaction->getTypeOfTransaction();

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

		$resultMini = '';
    	if (in_array($typeOfTransaction, $addPointArray)
    		&& $pointRemaining > 0
    		&& $status == Status::COMPLETE
    	) {
    		$resultMini = __('Expires on %1', $expiredTime);
    	}

    	if ($status == Status::PENDING) {
    		$statusLabel = Status::getLabel($status);
    		$result .= '<br/><span style="font-size: 11px; color:#808080; font-weight: bold;">'.$statusLabel.'</span>';
    	}

    	if ($resultMini != '') {
    		$result .= '<br/><span style="font-size: 11px; color:#808080; font-weight: bold;">'.$resultMini.'</span>';
    	}

		return $result;
	}

	public function getTransactionDetailNew($transaction)
	{
		$detail = $transaction->getTransactionDetail();
		$status = $transaction->getStatus();
		$pointRemaining = $transaction->getPointRemaining();
    	$addPointArray = $this->_type->getAddPointArray();
    	$typeOfTransaction = $transaction->getTypeOfTransaction();
		$usedPoints = $transaction->getAmount() - $pointRemaining;

    	$resultMini = '';
    	if (in_array($typeOfTransaction, $addPointArray)
    		&& $pointRemaining > 0
    		&& $status == Status::COMPLETE
    		&& $usedPoints != 0
    	) {
    		$resultMini = __('%1 points are available (Used %2 points)', $pointRemaining, $usedPoints);
    	}

    	$result = $this->_type->getTransactionDetail($typeOfTransaction, $detail, $status);
    	$br = '<br/>';
    	if ($typeOfTransaction == Type::CHECKOUT_ORDER_NEW) {
    		$br = '';
    	}

    	if ($resultMini != '') {
    		$result .= $br.'<span style="font-size: 11px; color:#808080; font-weight: bold;">'.$resultMini.'</span>';
    	}

		return $result;
	}

	public function formatAmount($amount, $type)
	{
		return Type::getAmountWithSign($amount, $type);
	}

	public function getPositiveAmount($amount, $type)
	{
		$result = Type::getAmountWithSign($amount, $type);
		if ($result > 0) {
			return $result;
		}

		return 0;
	}

	public function getStatusText($status)
	{
		return Status::getLabel($status);
	}

	public function getToolbarHtml()
	{
		return $this->getToolbar()->toHtml();
	}
}
