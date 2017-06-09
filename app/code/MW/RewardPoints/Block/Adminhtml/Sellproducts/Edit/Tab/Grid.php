<?php

namespace MW\RewardPoints\Block\Adminhtml\Sellproducts\Edit\Tab;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	protected $_single = false;

    protected $productId = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_setsFactory = $setsFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

        // Check is catalog product edit page
        if ($this->getRequest()->getModuleName() == 'catalog'
        	&& $this->getRequest()->getControllerName() == 'product'
        	&& $this->getRequest()->getActionName() == 'edit'
        ) {
            $this->setFilterVisibility(false);
            $this->setPagerVisibility(false);
        }
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    public function setSingle($flag)
    {
        $this->_single = $flag;
    }

    public function setFilterVisible($flag)
    {
        $this->setFilterVisibility($flag);
    }

    public function setPagerVisible($flag)
    {
        $this->setPagerVisibility($flag);
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        if ($this->productId != null) {
            $product = $this->_productFactory->create()->load($this->productId);
            switch ($product->getTypeId()) {
                case 'bundle': {
                    $collection = $product->getTypeInstance(true)->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product),
                        $product
                    )
                    ->addAttributeToSelect('sku')
                    ->addAttributeToSelect('mw_reward_point_sell_product')
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('attribute_set_id')
                    ->addAttributeToSelect('type_id');

                    $this->setCollection($collection);

                    parent::_prepareCollection();
                    break;
                }
            }
        } else {
            $store      = $this->_getStore();
            $collection = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('mw_reward_point_sell_product')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id')
                ->joinField('qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left');

            if ($store->getId()) {
                $collection->joinAttribute(
                    'name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    Store::DEFAULT_STORE_ID
                );
                $collection->joinAttribute(
                    'custom_name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
                $collection->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
                $collection->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                );
                $collection->joinAttribute(
                    'price',
                    'catalog_product/price',
                    'entity_id',
                    null,
                    'left',
                    $store->getId()
                );
            } else {
                $collection->addAttributeToSelect('price');
                $collection->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner'
                );
                $collection->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner'
                );
            }

            $this->setCollection($collection);

            parent::_prepareCollection();
            $this->getCollection()->addWebsiteNamesToResult();
        }

        return $this;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->_single == false) {
            if ($this->getCollection()) {
                if ($column->getId() == 'websites') {
                    $this->getCollection()->joinField(
                    	'websites',
                        'catalog/product_website',
                        'website_id',
                        'product_id=entity_id',
                        null,
                        'left'
					);
                }
            }

            return parent::_addColumnFilterToCollection($column);
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
        	'entity_id',
            [
                'header'=> __('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'sortable' => (!$this->_single) ? true : false,
           ]
        );
        $this->addColumn(
        	'name',
            [
                'header'=> __('Name'),
                'index' => 'name',
                'sortable' => (!$this->_single) ? true : false,
            ]
        );

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn(
            	'custom_name',
                [
                    'header'=> __('Name in %1', $store->getName()),
                    'index' => 'custom_name',
                    'sortable' => (!$this->_single) ? true : false,
                ]
            );
        }

        if ($this->_single == false) {
            $this->addColumn(
            	'type',
                [
                    'header'=> __('Type'),
                    'width' => '60px',
                    'index' => 'type_id',
                    'type'  => 'options',
                    'options' => $this->_type->getOptionArray(),
                    'sortable' => (!$this->_single) ? true : false,
                ]
            );

            $sets = $this->_setsFactory->create()->setEntityTypeFilter(
                $this->_productFactory->create()->getResource()->getTypeId()
            )->load()->toOptionHash();

            $this->addColumn(
                'set_name',
                [
                    'header'=> __('Attrib. Set Name'),
                    'width' => '100px',
                    'index' => 'attribute_set_id',
                    'type'  => 'options',
                    'options' => $sets,
                    'sortable' => (!$this->_single) ? true : false,
                ]
            );
        }

        $this->addColumn(
        	'sku',
            [
                'header'=> __('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'sortable' => (!$this->_single) ? true : false,
            ]
        );

        $this->addColumn(
        	'price',
            [
                'header'=> __('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'sortable' => (!$this->_single) ? true : false,
            ]
        );

        $this->addColumn(
        	'status',
            [
                'header'=> __('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => $this->_status->getOptionArray(),
                'sortable' => (!$this->_single) ? true : false,
            ]
        );

        if ($this->_single == false) {
            if (!$this->_storeManager->isSingleStoreMode()) {
                $this->addColumn(
                	'websites',
                    [
                        'header'=> __('Websites'),
                        'width' => '100px',
                        'sortable'  => false,
                        'index'     => 'websites',
                        'type'      => 'options',
                        'options'   => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                        'renderer' => 'MW\RewardPoints\Block\Adminhtml\Renderer\Website',
                        'sortable' => (!$this->_single) ? true : false,
                    ]
                );
            }
        }

        $this->addColumn(
        	'mw_reward_point_sell_product',
            [
                'header'=> __('Set Reward Points'),
                'width' => '50px',
                'type'  => 'number',
                'validate_class' => 'validate-number validate-digits',
                'index' => 'mw_reward_point_sell_product',
            	'edit_only' => true,
                'editable' => true,
                'sortable' => false,
                'renderer' => 'MW\RewardPoints\Block\Adminhtml\Renderer\Sellproduct',
            ]
        );

        if ($this->_single == false) {
            $this->addColumn(
            	'action',
                [
                    'header'    => __('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => [
                        [
                            'caption' => __('Edit'),
                            'target'  => '_blank',
                            'url'     => [
                                'base' => 'catalog/product/edit',
                                'params' => ['store' => $this->getRequest()->getParam('store')]
                            ],
                            'field'   => 'id'
                        ]
                    ],
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData(
            'grid_url'
        ) ? $this->getData(
            'grid_url'
        ) : $this->getUrl(
            '*/*/sellProductGrid',
            ['_current' => true]
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }
}