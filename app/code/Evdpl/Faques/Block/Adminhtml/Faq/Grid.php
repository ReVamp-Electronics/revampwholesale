<?php
namespace Evdpl\Faques\Block\Adminhtml\Faq;


use Evdpl\Faques\Model\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Evdpl\Faques\Model\QuestionFactory
     */
    protected $_questionFactory;

    /**
     * @var \SR\Weblog\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Evdpl\Faques\Model\QuestionFactory $questionFactory
     * @param \Evdpl\Faques\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Evdpl\Faques\Model\QuestionFactory $questionFactory,
        \Evdpl\Faques\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_questionFactory = $questionFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        //$this->setVarNameFilter('filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_questionFactory->create()->getCollection();

        $this->setCollection($collection);


        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                 'filter' => false,
                 'sortable'=> false,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'name'=>'entity_id'
            ]
        );
        $this->addColumn(
            'faq_question',
            [
                'header' => __('FAQ Question'),
                'index' => 'faq_question',
                'class' => 'xxx',
                 'filter' => false,
                 'sortable'=> false,
                'name'=>'faq_question'
            ]
        );

        $this->addColumn(
            'displayorder',
            [
                'header' => __('Display Order'),
                'index' => 'displayorder',
                 'filter' => false,
                 'sortable'=> false,
                'name'=>'displayorder'
            ]
        );


        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                 'filter' => false,
                 'sortable'=> false,
                'index' => 'status',
                'type' => 'options',
                'name'=>'status',
                'options' => $this->_status->getOptionArray()
            ]
        );


        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'entity_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */

    
    protected function _prepareMassaction()
    {
        
        $this->setMassactionIdField('entity_id');
//    $this->getMassactionBlock()->setTemplate('Evdpl_Faques::faq/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('question');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('faques/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('faques/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('faques/*/grid', ['_current' => true]);
    }

    /**
     * @param \SR\Weblog\Model\BlogPosts|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'faques/*/edit',
            array('store' => $this->getRequest()->getParam('store'), 'entity_id' => $row->getId())
        );
    }
    
}