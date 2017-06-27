<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponentInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const DEFAULT_COLUMNS_MAX_ORDER = 100;
    
    protected $attributeRepository;
    protected $inlineEditUpdater;
    protected $helper;
    protected $bookmarkManagement;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    protected $skipAttributes = [
        'old_id',
        'tier_price',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'category_ids',
        'options_container',
        'required_options',
        'has_options',
        'image_label',
        'small_image_label',
        'thumbnail_label',
        'created_at',
        'updated_at',
        'quantity_and_stock_status',
        'msrp',
        'msrp_display_actual_price_type',
        'price_view',
        'url_key',
        'url_path',
        'weight_type',
//        'tax_class_id',
        'category_gear'
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Amasty\Pgrid\Ui\Component\ColumnFactory $columnFactory,
        \Amasty\Pgrid\Ui\Component\Listing\Attribute\Repository $attributeRepository,
        \Amasty\Pgrid\Ui\Component\Listing\Column\InlineEditUpdater $inlineEditUpdater,
        \Amasty\Pgrid\Helper\Data $helper,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->inlineEditUpdater = $inlineEditUpdater;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->helper = $helper;
    }

    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }

    public function prepare()
    {
        $visibleColumns = $this->_getVisibleColumns();

        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;

        foreach ($this->attributeRepository->getList() as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()]) && !in_array($attribute->getAttributeCode(), $this->skipAttributes)) {
                $config['sortOrder'] = ++$columnSortOrder;
                $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
                $config['isFilterableInGrid'] = $attribute->getIsFilterableInGrid();
                $config['amastyAttribute'] = true;
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                if (array_key_exists($attribute->getAttributeCode(), $visibleColumns)) {
                    $column->prepare();
                }

                $this->inlineEditUpdater->applyEditing(
                    $column,
                    $attribute->getFrontendInput(),
                    $attribute->getFrontendClass(),
                    $attribute->getIsRequired()
                );
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }

        $this->_prepareConfig();
        $this->_prepareColumns();

        parent::prepare();
    }

    protected function _getVisibleColumns()
    {
        $visibleColumns = [];

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'product_listing'
        );

        if (is_object($bookmark)) {
            $config = $bookmark->getConfig();
            if (isset($config['current']['columns']) && is_array($config['current']['columns'])) {
                foreach ($config['current']['columns'] as $key => $column) {
                    if ($column['visible'] == true) {
                        $visibleColumns[$key] = $column;
                    }
                }
            }
        }

        return $visibleColumns;
    }

    protected function _prepareConfig()
    {
        $config = $this->getConfig();

        if (isset($config['amastyEditorConfig'])){
            $config['amastyEditorConfig']['isMultiEditing'] = (string)$this->helper->getModuleConfig('editing/mode') == 'multi' ;
        }
        $this->setConfig($config);
    }

    protected function _prepareColumns(){
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            'product_listing'
        );

        $config = $bookmark? $bookmark->getConfig() : null;

        $bookmarksCols = is_array($config) && is_array($config['current']) && is_array($config['current']['columns']) ? $config['current']['columns'] : array();

        foreach($this->components as $id => $column){
            if ($column instanceof \Magento\Ui\Component\Listing\Columns\Column){
                $config = $column->getData('config');

                $hasFilter = isset($config['filter']);

                if ($hasFilter){
                    $config['default_filter'] = $config['filter'];
                }

                $filter = $hasFilter ? $config['filter'] : null;

                $filterHidden = isset($bookmarksCols[$id]['ampgrid_filterable']) && $bookmarksCols[$id]['ampgrid_filterable'] === false;

                $isFilterableInGrid = isset($config['isFilterableInGrid']) ? $config['isFilterableInGrid'] : true;

                if ($filterHidden ||
                    (!$isFilterableInGrid && !isset($bookmarksCols[$id]['ampgrid_filterable']))) {
                    $filter = '';
                }

                $config['filter'] = $filter;

                $config['ampgrid'] = array(
                    'visible' => isset($config['visible']) ? $config['visible'] : false,
                    'has_editor' => isset($config['editor']),
                    'has_filter' => $hasFilter,
                    'filterable' => isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['ampgrid_filterable']) ? $bookmarksCols[$id]['ampgrid_filterable'] : !empty($config['filter']),
                    'editable' => isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['ampgrid_editable']) ? $bookmarksCols[$id]['ampgrid_editable'] : false,
                    'title' => isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['ampgrid_title']) ? $bookmarksCols[$id]['ampgrid_title'] : (isset($config['label']) ? $config['label'] : ''),
                    'visible' => isset($bookmarksCols[$id]) && isset($bookmarksCols[$id]['visible']) ? $bookmarksCols[$id]['visible'] : (isset($config['visible']) ? $config['visible'] : true),

                );


                $config['ampgrid_def_label'] = isset($config['label']) ? $config['label'] : '';
                $config['label'] = $config['ampgrid']['title'];
                $config['ampgrid_editable'] = $config['ampgrid']['editable'];


                $column->setData('config', $config);
            }
        }
    }
}
