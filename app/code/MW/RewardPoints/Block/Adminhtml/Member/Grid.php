<?php

namespace MW\RewardPoints\Block\Adminhtml\Member;

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
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Backend\Helper\Data $backendHelper
	 * @param \Magento\Framework\App\ResourceConnection $resource
	 * @param \Magento\Customer\Model\CustomerFactory $customerFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		array $data = []
	) {
		parent::__construct($context, $backendHelper, $data);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
	}

	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('member_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
    	$collection = $this->_customerFactory->create()->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email');
        $customerTable = $this->_resource->getTableName('mw_reward_point_customer');

        $collection->getSelect()->joinLeft(
            ['reward_customer_entity' => $customerTable],
            'e.entity_id = reward_customer_entity.customer_id',
            ['mw_reward_point']
		);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
		$this->addColumn(
			'entity_id',
			[
				'header'    => __('ID'),
				'align'     =>'right',
				'width'     => '100px',
				'index'     => 'entity_id'
			]
		);
		$this->addColumn(
			'name',
			[
				'header'    => __('Customer Name'),
				'index'     => 'name'
			]
		);
		$this->addColumn(
			'email',
			[
				'header'    => __('Customer Email'),
				'align'     =>'left',
				'index'     => 'email'
			]
		);
		$this->addColumn(
			'reward_point',
			[
				'header'    => __('Balance'),
				'align'     => 'right',
				'width'     => '80px',
				'index'     => 'mw_reward_point',
				'type'      => 'number',
				'renderer'  => 'MW\RewardPoints\Block\Adminhtml\Renderer\Point',
                'filter_condition_callback' => [$this, '_filterPointCondition'],
			]
		);

		$this->addColumn('action',
			[
				'header'    =>  __('Action (Manage Points)'),
				'width'     => '80px',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => [
					[
						'caption'   => __('View'),
						'url'       => ['base'=> '*/*/edit'],
						'field'     => 'id'
					]
				],
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
			]
		);

		$this->addExportType('*/*/exportCsv', __('CSV'));
		$this->addExportType('*/*/exportXml', __('XML'));

		return parent::_prepareColumns();
    }

	protected function _filterPointCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
		    return;
		}

		if (isset($value['from']) && $value['from'] != '' && $value['from'] != 0) {
		    $this->getCollection()->getSelect()->where("reward_customer_entity.mw_reward_point >= ?", $value['from']);
		}

		if (isset($value['to']) && $value['to'] != '') {
		    $this->getCollection()->getSelect()->where("reward_customer_entity.mw_reward_point <= ?", $value['to']);
		}
	}

	public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
