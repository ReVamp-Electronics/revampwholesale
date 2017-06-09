<?php

namespace MW\RewardPoints\Block\Sales\Order;

class Total extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MW\RewardPoints\Model\RewardpointsorderFactory
     */
    protected $_rwpOrderFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\RewardPoints\Model\RewardpointsorderFactory $rwpOrderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_rwpOrderFactory = $rwpOrderFactory;
    }

	/**
     * Get label cell tag properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get order store object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get value cell tag properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize reward points totals
     *
     * @return \Enterprise\Reward\Block\Sales\Order\Total
     */
    public function initTotals()
    {
 		$total = new \Magento\Framework\DataObject(
 			[
	            'code'      => $this->getNameInLayout(),
	            'block_name'=> $this->getNameInLayout(),
	            'area'      => $this->getArea()
            ]
        );

        $after = $this->getAfterTotal();
        if (!$after) {
            $after = 'discount';
        }

        $this->getParentBlock()->addTotal($total, $after);

        return $this;
    }

    /**
     * @return \MW\RewardPoints\Model\Rewardpointsorder
     */
    public function getRewardOrder()
    {
        return $this->_rwpOrderFactory->create();
    }
}
