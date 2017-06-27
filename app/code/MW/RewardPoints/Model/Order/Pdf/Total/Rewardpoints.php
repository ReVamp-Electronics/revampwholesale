<?php

namespace MW\RewardPoints\Model\Order\Pdf\Total;

class Rewardpoints extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
	/**
	 * @var \Magento\Directory\Helper\Data
	 */
	protected $_directory;

	/**
	 * @var \MW\RewardPoints\Model\RewardpointsorderFactory
	 */
	protected $_rwpOrdersFactory;

	/**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Directory\Helper\Data $directory
     * @param \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrdersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Directory\Helper\Data $directory,
        \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrdersFactory,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->_directory = $directory;
        $this->_rwpOrdersFactory = $rwpOrdersFactory;
    }

	/**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
    	$_order = $this->getOrder();
    	$baseCurrencyCode = $_order->getBaseCurrencyCode();
 		$currentCurrencyCode = $_order->getOrderCurrencyCode();

 		$rewardOrder = $this->_rwpOrdersFactory->create()->load($_order->getId());
		$rewardpointDiscountShow = $_order->getMwRewardpointDiscountShow();
 		if ($rewardpointDiscountShow == 0) {
 			$rewardpointDiscountShow = $this->_directory->currencyConvert(
 				$rewardOrder->getMoney(),
 				$baseCurrencyCode,
 				$currentCurrencyCode
 			);
 		}

    	return $rewardpointDiscountShow;
    }
}
