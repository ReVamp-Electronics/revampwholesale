<?php

namespace MW\RewardPoints\Block\Adminhtml\History;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $_resource;

	/**
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	protected $_customerFactory;

	/**
	 * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
	 */
	protected $_historyFactory;

	/**
	 * @var \MW\RewardPoints\Model\Status
	 */
	protected $_status;

	/**
	 * @var \MW\RewardPoints\Model\Typecsv
	 */
	protected $_typeCSV;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Helper\Data $backendHelper
	 * @param \Magento\Framework\App\ResourceConnection $resource
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
	 * @param \MW\RewardPoints\Model\Status $status
	 * @param \MW\RewardPoints\Model\Typecsv $typeCSV
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
		\MW\RewardPoints\Model\Status $status,
		\MW\RewardPoints\Model\Typecsv $typeCSV,
		array $data = []
	) {
		parent::__construct($context, $backendHelper, $data);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
		$this->_historyFactory = $historyFactory;
		$this->_status = $status;
		$this->_typeCSV = $typeCSV;
	}

	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('historyGrid');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('desc');
        $this->setEmptyText(__('No Transaction Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
  	  	$customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_historyFactory->create()->getCollection()
			->setOrder('transaction_time', 'DESC')
			->setOrder('history_id', 'DESC');

		$collection->getSelect()->join(
			['customer_entity' => $customerTable],
			'main_table.customer_id = customer_entity.entity_id',
			['email']
		);

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
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
				'header'    =>  __('Created Time'),
				'type'      => 'datetime',
				'align'     => 'center',
				'index'     => 'transaction_time',
				'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Time'
			]
		);
		$this->addColumn(
			'customer_name',
			[
				'header'    => __('Customer Name'),
				'align'     => 'left',
				'index'     => 'customer_id',
				'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Name',
				'filter_condition_callback' => [$this, '_filterReferralnameCondition']
			]
		);
		$this->addColumn(
			'email',
			[
				'header'    => __('Customer Email'),
				'align'     => 'left',
				'index'     => 'email'
			]
		);
		$this->addColumn(
			'amount',
			[
				'header'    => __('Amount'),
				'align'     => 'right',
				'index'     => 'amount',
				'type'      => 'number',
				'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Amount'
			]
		);
		$this->addColumn(
			'balance',
			[
				'header'    => __('Customer Balance'),
				'align'     => 'right',
				'index'     => 'balance',
				'type'      => 'number'
			]
		);
		$this->addColumn(
			'transaction_detail',
			[
				'header'    => __('Transaction Detail'),
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

		$this->addExportType('*/*/exportCsv', __('CSV'));
		$this->addExportType('*/*/exportXml', __('XML'));

		return parent::_prepareColumns();
    }

    protected function _filterReferralnameCondition($collection, $column)
    {
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$customer_ids = [];
		$value = '%'.$value.'%';

		$customerCollection = $this->_customerFactory->create()
			->getCollection()
			->addAttributeToFilter(
				[
					[
						'attribute' => 'firstname',
						['like' => $value]
					],
					[
						'attribute' => 'firstname',
						['like' => $value]
					]
				]
			);

		$customerIds = [];
		foreach ($customerCollection as $customer) {
			$customerIds[] = $customer->getId();
		}

		$this->getCollection()->getSelect()->where("main_table.customer_id in (?)", $customerIds);
    }

    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->_columns as $col_id => $column) {
                if (!$column->getIsSystem()) {
                	if ($col_id == 'transaction_detail') {
                    	$transactionDetail = $this->_typeCSV->getTransactionDetail(
                    		$item->getTypeOfTransaction(),
                    		$item->getTransactionDetail(),
                    		$item->getStatus(),
                    		true
                    	);
                    	$data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $transactionDetail).'"';
                    } else {
                    	$data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($item)).'"';
                    }
                }
            }
            $csv.= implode(',', $data)."\n";
            unset($data);
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($this->getTotals())).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        return $csv;
    }
}
