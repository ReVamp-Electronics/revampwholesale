<?php

namespace MW\RewardPoints\Block\Adminhtml\Member\Edit\Tab;

class Transaction extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \MW\RewardPoints\Model\Status
	 */
	protected $_status;

	/**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\Status $status
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\MW\RewardPoints\Model\Status $status,
		array $data = []
	) {
		parent::__construct($context, $backendHelper, $data);
		$this->_historyFactory = $historyFactory;
		$this->_status = $status;
	}

	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('Rewardpoints_Grid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Transaction Found'));
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
    	return $this->getUrl(
    		'rewardpoints/member/transaction',
    		['id' => $this->getRequest()->getParam('id')]
    	);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
    	$collection = $this->_historyFactory->create()->getCollection()
           	->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))
			->setOrder('transaction_time', 'DESC')
			->setOrder('history_id', 'DESC');

      	$this->setCollection($collection);
      	return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
    	$this->addColumn(
    		'history_id',
    		[
	            'header'    => __('ID'),
	            'align'     => 'left',
	            'index'     => 'history_id',
	            'width'     => 10
            ]
        );
        $this->addColumn(
        	'transaction_time',
        	[
	            'header'    => __('Transaction Time'),
	            'type'      => 'datetime',
	            'align'     => 'center',
	            'index'     => 'transaction_time',
	            'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Time'
            ]
        );
        $this->addColumn(
        	'amount',
        	[
	            'header'    => __('Amount'),
	            'align'     => 'left',
	            'index'     => 'amount',
	        	'type'      => 'number',
	        	'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Amount'
        	]
        );
        $this->addColumn(
        	'balance',
        	[
	            'header'    => __('Customer Balance'),
	            'align'     => 'left',
	            'index'     => 'balance',
	        	'type'      => 'number'
        	]
        );
        $this->addColumn(
        	'transaction_detail',
        	[
	            'header'    => __('Transaction Details'),
	            'align'     => 'left',
	        	'width'		=> 400,
	            'index'     => 'transaction_detail',
	        	'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Transaction'
        	]
        );
      	$this->addColumn(
      	 	'status',
      	 	[
	          	'header'    => __('Status'),
	          	'align'     => 'center',
	          	'index'     => 'status',
			  	'type'      => 'options',
	          	'options'   => $this->_status->getOptionArray()
          	]
      	);

      	return parent::_prepareColumns();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Transaction History');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Transaction History');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
