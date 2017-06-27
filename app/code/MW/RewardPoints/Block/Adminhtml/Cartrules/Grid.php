<?php

namespace MW\RewardPoints\Block\Adminhtml\Cartrules;

use MW\RewardPoints\Model\Statusrule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MW\RewardPoints\Model\CartrulesFactory
     */
    protected $_cartrulesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\RewardPoints\Model\CartrulesFactory $cartrulesFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_cartrulesFactory = $cartrulesFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cart_rule_Grid');
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('DESC');
        $this->setEmptyText(__('No Shopping Cart Earning Rule Found'));
    }

    protected function _prepareCollection()
    {
        $collection = $this->_cartrulesFactory->create()->getCollection();
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
        $this->getMassactionBlock()->setFormFieldName('cart_rule_Grid');

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
