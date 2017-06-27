<?php

namespace MW\RewardPoints\Block\Adminhtml\Activerules;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Statusrule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_activerulesFactory = $activerulesFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('activerules_grid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collections = $this->_activerulesFactory->create()->getCollection();
        $this->setCollection($collections);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'rule_id',
            [
                'header' => __('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'rule_id',
            ]
        );
        $this->addColumn(
            'rule_name',
            [
                'header' => __('Rule name'),
                'align'  => 'left',
                'index'  => 'rule_name',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'type_of_transaction',
            [
                'header'  => __('Transaction Type'),
                'align'   => 'left',
                'index'   => 'type_of_transaction',
                'type'    => 'options',
                'options' => Type::getTypeReward(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode())
        {
            $this->addColumn(
                'store_view',
                [
                    'header'                    => __('Store View'),
                    'index'                     => 'store_view',
                    'type'                      => 'store',
                    'store_all'                 => true,
                    'store_view'                => true,
                    'sortable'                  => false,
                    'filter_condition_callback' => [$this, '_filterStoreCondition']
                ]
            );
        }

        $this->addColumn(
            'reward_point',
            [
                'header' => __('Set Reward Points'),
                'align'  => 'left',
                'index'  => 'reward_point',
                'type'   => 'text',
                'width'  => '150px',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'align'   => 'left',
                'width'   => '120px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Statusrule::getOptionArray(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption' => __('View'),
                        'url'     => ['base' => '*/*/edit'],
                        'field'   => 'id'
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

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('activerules_grid');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'   => __('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $status = Statusrule::getOptionArray();
        array_unshift($status, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label'      => __('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => __('Status'),
                        'values' => $status
                    ]
                ]
            ]
        );

        return $this;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()->where("main_table.store_view LIKE '%" . $value . "%' OR main_table.store_view = '0'");
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
