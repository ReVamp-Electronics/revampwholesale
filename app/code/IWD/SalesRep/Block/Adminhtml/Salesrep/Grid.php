<?php

namespace IWD\SalesRep\Block\Adminhtml\Salesrep;

use \IWD\SalesRep\Model\ResourceModel\User\CollectionFactory as SalesrepUserCollectionFactory;
use \IWD\SalesRep\Model\User as SalesrepUser;
use \IWD\SalesRep\Model\Customer as SalesrepAssignedCustomer;
use \IWD\SalesRep\Helper\Data as SalesrepHelper;

/**
 * Class Grid
 * @package IWD\SalesRep\Block\Adminhtml\Salesrep
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var SalesrepUserCollectionFactory
     */
    private $salesrepUserCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SalesrepHelper
     */
    private $salesrepHelper;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param SalesrepUserCollectionFactory $salesrepUserCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param SalesrepHelper $salesrepHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        SalesrepUserCollectionFactory $salesrepUserCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        SalesrepHelper $salesrepHelper,
        array $data = []
    ) {
        $this->salesrepUserCollectionFactory = $salesrepUserCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->salesrepHelper = $salesrepHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('salesrepBlockGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('salesrep_filter');
        $this->setNoFilterMassactionColumn(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $adminUserTable = $this->resourceConnection->getTableName('admin_user');
        $collection = $this->salesrepUserCollectionFactory->create();
        $select = $collection->getSelect();

        $select->joinInner(
            ['admin_user'=>$adminUserTable],
            'main_table.'.SalesrepUser::ADMIN_ID.' = admin_user.user_id',
            ['admin_user.firstname', 'admin_user.lastname']
        )
        ->joinLeft(
            ['assigned_customers' => $this->resourceConnection->getTableName(\IWD\SalesRep\Model\ResourceModel\Customer::TABLE_NAME)],
            'main_table.' . SalesrepUser::SALESREP_ID . ' = assigned_customers.' . SalesrepAssignedCustomer::SALESREP_ID,
            ['assigned_customers_count' => 'count(assigned_customers.entity_id)']
        )
        ->group('main_table.' . SalesrepUser::SALESREP_ID);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'assigned_customers_count') {
                $select = $this->getCollection()->getSelect();
                $havingPart = $select->getPart('having');
                $value = $column->getFilter()->getEscapedValue();
                $havingPart[] = 'count(assigned_customers.entity_id) = ' . $value;
                $select->setPart('having', $havingPart);
            } else {
                if ($column->getId() == 'entity_id') {
                    $value = $column->getFilter()->getEscapedValue();
                    
                    $select = $this->getCollection()->getSelect();
                    $wherePart = $select->getPart('where');
                    $wherePart[] = "main_table.entity_id LIKE '%{$value}%' ";
                    $select->setPart('where', $wherePart);
                } else {
                    return parent::_addColumnFilterToCollection($column);
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            SalesrepUser::SALESREP_ID,
            [
                'header' => __('Sales Rep ID'),
                'index' => SalesrepUser::SALESREP_ID,
            ]
        );

        $this->addColumn(
            'firstname',
            [
                'header' => __('First Name'),
                'index' => 'firstname',
            ]
        );

        $this->addColumn(
            'lastname',
            [
                'header' => __('Last Name'),
                'index' => 'lastname',
            ]
        );

        $this->addColumn(
            'assigned_customers_count',
            [
                'header' => __('Number of Assigned Customers'),
                'index' => 'assigned_customers_count',
                'filter' => false,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->setData('filter', false);
        $this->getMassactionBlock()->unsetData('filter');
                
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'=> 'Delete (only sales representatives)',
                'url'  => $this->getUrl('*/*/massDelete', ['' => '']),
                'confirm' => 'Are you sure?'
            ]
        );

        $deleteAllAccountsLbl = '';
        if ($this->salesrepHelper->isWithB2B()) {
            $deleteAllAccountsLbl = '/B2B';
        }

        $this->getMassactionBlock()->addItem(
            'delete_with_admin',
            [
                'label'=> "Delete (with admin$deleteAllAccountsLbl accounts)",
                'url'  => $this->getUrl('*/*/massDelete', ['all_accounts' => '1']),
                'confirm' => "Are you sure? This action will delete admin user"
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url')
            ? $this->_getData('grid_url')
            : $this->getUrl('salesrep/salesrep/search');
    }

    /**
     * @inheritdoc
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/user/edit', [
            'user_id' => $row->getData(SalesrepUser::ADMIN_ID),
            SalesrepHelper::HTTP_REFERRER_KEY => SalesrepHelper::HTTP_REFERRER
        ]);
    }
}
