<?php

namespace IWD\SalesRep\Block\Adminhtml\User\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Registry;
use IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer\Collection as SalesRepCustomerCollection;

/**
 * Class SalesRepCustomers
 * @package IWD\SalesRep\Block\Adminhtml\User\Edit\Tab
 */
class SalesRepCustomers extends Extended
{
    const CSS_CLASS_ROW_DISABLED = 'iwdsr-disabled';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory
     */
    private $salesrepAttachedCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var \Magento\Store\Model\System\StoreFactory
     */
    private $storeFactory;

    /**
     * @var null
     */
    private $engagedCustomerIds = null;

    /**
     * @var null
     */
    private $assignedCustomerIds = null;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SalesRepCustomers constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAttachedCustomerCollectionFactory
     * @param Registry $registry
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory
     * @param \Magento\Store\Model\System\StoreFactory $storeFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \IWD\SalesRep\Model\ResourceModel\Customer\CollectionFactory $salesrepAttachedCustomerCollectionFactory,
        Registry $registry,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \Magento\Store\Model\System\StoreFactory $storeFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->registry = $registry;
        $this->salesrepAttachedCustomerCollectionFactory = $salesrepAttachedCustomerCollectionFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->storeFactory = $storeFactory;
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('iwd_salesrep_customers');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter(SalesRepCustomerCollection::KEY_B2B_MASTER_SALESREP_ID, new \Zend_Db_Expr('NULL'));

        $attachedCustomerTable = $this->resource->getTableName('iwd_sales_representative_attached_customer');
        $representativeUser = $this->resource->getTableName('iwd_sales_representative_user');
        $adminUserTable = $this->resource->getTableName('admin_user');
        
        $collection->getSelect()->joinLeft(
            ["attached_customer" => $attachedCustomerTable],
            "e.entity_id = attached_customer.customer_id",
            []
        )->joinLeft(
            ["representative_user" => $representativeUser],
            "representative_user.entity_id = attached_customer.salesrep_id",
            []
        )->joinLeft(
            ["admin_user" => $adminUserTable],
            "admin_user.user_id = representative_user.admin_user_id",
            []
        )->columns(
            [
                'name' => new \Zend_Db_Expr("CONCAT(`e`.`firstname`, ' ', `e`.`lastname`)"),
                'admin_user' => new \Zend_Db_Expr("CONCAT(`admin_user`.`firstname`, ' ', `admin_user`.`lastname`)")
            ]
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'is_assigned') {
            $this->getCollection()->addFieldToFilter('is_assigned', $column->getFilter()->getValue());
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'is_assigned',
            [
                'type' => 'checkbox',
                'name' => 'is_assigned',
                'values' => $this->getAttachedCustomers(),
                'disabled_values' => $this->getEngagedCustomers(),
                'align' => 'center',
                'index' => 'entity_id',
                'header_css_class' => 'col-select data-grid-multicheck-cell',
                'column_css_class' => 'iwd-salesrep-assign data-grid-checkbox-cell'
            ]
        );
        $this->addColumn(
            'customer_entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'iwd-sr-col-id',
                'column_css_class' => 'iwd-sr-col-id'
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'name',
                'header_css_class' => 'iwd-sr-col-attr-name',
                'column_css_class' => 'iwd-sr-col-attr-name'
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );

        $groups = $this->customerGroupCollectionFactory->create()
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'customer_group',
            [
                'header' => __('Group'),
                'index' => 'group_id',
                'type'      =>  'options',
                'options'   =>  $groups,
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'customer_website',
                [
                    'header' => __('Website'),
                    'index' => 'website_id',
                    'type'      =>  'options',
                    'options'   =>  $this->storeFactory->create()->getWebsiteOptionHash(true),
                ]
            );
        }

        $this->addColumn('salesrep_action', [
            'header' => __('Commission'),
            'sortable' => false,
            'filter' => false,
            'renderer'  => '\IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer\Commission',
        ]);

        $this->addColumn(
            'admin_user',
            [
                'header' => __('Sales Rep'),
                'index' => 'admin_user',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return array|null
     */
    private function getAttachedCustomers()
    {
        if ($this->assignedCustomerIds === null) {
            $adminUser = $this->registry->registry('admin_user');
            $attached = $this->salesrepAttachedCustomerCollectionFactory->create()
                ->addFieldToFilter(
                    'salesrep_id',
                    $adminUser->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID)
                );

            $values = [];
            foreach ($attached as $att) {
                $values[] = $att->getData(\IWD\SalesRep\Model\Customer::CUSTOMER_ID);
            }
            $this->assignedCustomerIds = $values;
        }

        return $this->assignedCustomerIds;
    }

    /**
     * @return array|null
     */
    private function getEngagedCustomers()
    {
        if ($this->engagedCustomerIds === null) {
            $adminUser = $this->registry->registry('admin_user');
            $engaged = $this->salesrepAttachedCustomerCollectionFactory->create()
                ->addFieldToFilter(
                    'salesrep_id',
                    ['neq' => $adminUser->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID)]
                );

            $values = [];
            foreach ($engaged as $att) {
                $values[] = $att->getData(\IWD\SalesRep\Model\Customer::CUSTOMER_ID);
            }

            $this->engagedCustomerIds = $values;
        }

        return $this->engagedCustomerIds;
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('salesrep/user/customers', ['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    public function getRowUrl($item)
    {
        return null;
    }

    /**
     * @param $item
     * @return string
     */
    public function getRowClass($item)
    {
        return $this->isRowDisabled($item) ? self::CSS_CLASS_ROW_DISABLED : '';
    }

    /**
     * @param $item
     * @return bool
     */
    private function isRowDisabled($item)
    {
        /**
         * passed \IWD\SalesRep\Controller\Adminhtml\User\Customers
         * @var $adminUser \Magento\User\Model\User
         */
        $engagedCustomerIds = $this->getEngagedCustomers();
        return in_array($item->getId(), $engagedCustomerIds);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->getRequest()->getParam('user_id') !== null) {
            return parent::toHtml();
        }

        return '<p>You can assign customers to Sales Representative after saving the user</p>';
    }
}
