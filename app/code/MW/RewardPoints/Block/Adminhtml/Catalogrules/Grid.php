<?php

namespace MW\RewardPoints\Block\Adminhtml\Catalogrules;

use MW\RewardPoints\Model\Statusrule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
     * @var \MW\RewardPoints\Model\CatalogrulesFactory
     */
    protected $_catalogrulesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\RewardPoints\Model\CatalogrulesFactory $catalogrulesFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_catalogrulesFactory = $catalogrulesFactory;
    }

	/**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_rules_Grid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		$this->setEmptyText(__('No Catalog Reward Rules Found'));
    }

    protected function _prepareCollection()
    {
        $collection = $this->_catalogrulesFactory->create()->getCollection();
        $this->setCollection($collection);

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
            'name',
            [
                'header' => __('Rule Name'),
                'align'  => 'left',
                'index'  => 'name',
            ]
        );
        $this->addColumn(
            'start_date',
            [
                'header' => __('Start Date'),
                'width'  => '150px',
                'index'  => 'start_date',
            ]
        );
        $this->addColumn(
            'end_date',
            [
                'header' => __('End Date'),
                'width'  => '150px',
                'index'  => 'end_date',
            ]
        );
        $this->addColumn(
            'rule_position',
            [
                'header' => __('Priority'),
                'align'  => 'left',
                'type'   => 'number',
                'index'  => 'rule_position',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'  => __('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Statusrule::getOptionArray(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption' => __('Edit'),
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
        $this->getMassactionBlock()->setFormFieldName('catalog_rules_grid');

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

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
