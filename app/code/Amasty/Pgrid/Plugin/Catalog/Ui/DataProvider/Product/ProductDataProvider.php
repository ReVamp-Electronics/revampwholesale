<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Plugin\Catalog\Ui\DataProvider\Product;

class ProductDataProvider
{
    protected $_columns = array(
        'amasty_categories',
        'amasty_link',
        'amasty_availability',
        'amasty_created_at',
        'amasty_updated_at',
        'amasty_related_products',
        'amasty_up_sells',
        'amasty_cross_sells',
        'amasty_low_stock'
    );
    protected $_categoryColFactory;
    protected $_url;
    protected $_categoriesPath;
    protected $_bookmarkManagement;


    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Ui\Api\BookmarkManagementInterface $bookmarkManagement,
        \Amasty\Pgrid\Helper\Data $helper
    ){
        $this->_categoryColFactory = $categoryColFactory;
        $this->_url = $url;
        $this->_bookmarkManagement = $bookmarkManagement;
        $this->_helper = $helper;
    }

    protected function _getCategories($row)
    {
        $categoriesHtml = '';
        $categories     = $row->getCategoryCollection()->addNameToResult();
        if ($categories)
        {
            foreach ($categories as $category)
            {
                $path        = '';
                $pathInStore = $category->getPathInStore();
                $pathIds     = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path .= $categories[$categoryId]->getName() . '/';
                    }
                }

                if ($path)
                {
                    $path = substr($path, 0, -1);
                    $path = '<div style="font-size: 90%; margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">' . $path . '</div>';
                }

                $categoriesHtml .= $path;
            }
        }
        return $categoriesHtml;
    }

    protected function _getVisibleColumns($configData)
    {
        $ret = array(
            'price'
        );

        if (isset($configData['amasty_columns']) && is_array($configData['amasty_columns'])) {
            foreach ($configData['amasty_columns'] as $key) {
                if (in_array($key, $this->_columns)) {
                    $ret[] = $key;
                }
            }
        }

        $activeBookmark = $this->_getActiveBookmark();

        if (isset($activeBookmark['current']['columns'])) {
            foreach ($activeBookmark['current']['columns'] as $key => $column) {
                if (isset($column['visible']) && $column['visible'] && in_array($key, $this->_columns)) {
                    $ret[] = $key;
                }
            }
        }

        return $ret;
    }

    protected function _getActiveBookmark(){
        $bookmarks = $this->_bookmarkManagement->loadByNamespace('product_listing');


        $activeBookmark = [];
        $config = [];
        /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->isCurrent()) {
                $config['activeIndex'] = $bookmark->getIdentifier();
                $activeBookmark = $config;
            }

            $config = array_merge_recursive($config, $bookmark->getConfig());
        }

        return $activeBookmark;
    }

    public function beforeGetData(
            \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject
    ){
        $activeBookmark = $this->_getActiveBookmark();

        if ($this->_isColumnVisible($activeBookmark, 'amasty_categories') ||
            $this->_isColumnVisible($activeBookmark, 'amasty_link')){
            $subject->getCollection()->addUrlRewrite();
        }

        if ($this->_isColumnVisible($activeBookmark, 'amasty_availability')){
            $subject->getCollection()->joinField(
                'amasty_availability',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        if ($this->_isColumnVisible($activeBookmark, 'amasty_low_stock')){
            $this->_addLowStock($subject->getCollection());
        }
    }

    protected function _addLowStock($collection){
        $configManageStock = (int) $this->_helper->getScopeValue(\Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK);

        $globalNotifyStockQty = (float) $this->_helper->getScopeValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_NOTIFY_STOCK_QTY);


        $stockItemWhere = '({{table}}.low_stock_date is not null) '
            . " AND ( ({{table}}.use_config_manage_stock=1 AND {$configManageStock}=1)"
            . " AND {{table}}.qty < "
            . "IF(amasty_low_stock_item.`use_config_notify_stock_qty`, {$globalNotifyStockQty}, {{table}}.notify_stock_qty)"
            . ' OR ({{table}}.use_config_manage_stock=0 AND {{table}}.manage_stock=1) )';

        $collection
            ->addAttributeToSelect('name', true)
            ->joinTable(array(
                'amasty_low_stock_item' => 'cataloginventory_stock_item'
                ), 'product_id=entity_id',
                array(
                     'if(amasty_low_stock_item.item_id IS NULL, 0 , 1) as amasty_low_stock'
                    ),
                $stockItemWhere, 'left')
            ->setOrder('amasty_low_stock_item.low_stock_date');
    }

    protected function _isColumnVisible($bookmark, $column){
        return isset($bookmark['current']['columns']) &&
            isset($bookmark['current']['columns'][$column]) &&
            isset($bookmark['current']['columns'][$column]['visible']) &&
            $bookmark['current']['columns'][$column]['visible'];
    }

    protected function _initCategories($collection, &$result)
    {
        $idx = 0;

        foreach($collection as $product){

            $amastyCategories = null;

            if (isset($result['items']) && isset($result['items'][$idx])){
                $amastyCategories = $this->_getCategories($product);
            }

            $result['items'][$idx]['amasty_categories'] = $amastyCategories;
            $idx++;
        }
    }

    protected function _initExtra(&$row, $column){
        switch($column){
            case "amasty_link":
                if (isset($row['request_path'])){
                    $row[$column] = $this->_url->getUrl('', array(
                        '_direct' => $row['request_path']
                    ));
                }
                break;
            case "amasty_created_at":
                $row[$column] = $row['created_at'];
                break;
            case "amasty_updated_at":
                $row[$column] = $row['updated_at'];
                break;
        }
    }

    protected function _initRelatedProducts($productsCollection, $column, &$result)
    {
        $idx = 0;

        foreach($productsCollection as $product){
            $ret = '';
            $collection = NULL;

            switch ($column){
                case "amasty_related_products":
                    $collection = $product->getRelatedProductCollection();
                    break;
                case "amasty_up_sells":
                    $collection = $product->getUpSellProductCollection();
                    break;
                case "amasty_cross_sells":
                    $collection = $product->getCrossSellProductCollection();
                    break;
            }

            $qty = $this->_helper->getModuleConfig('extra_columns/products_qty');

            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'left');
            $collection->setPageSize($qty);

            $items = $collection->getItems();

            if ($items){

                foreach ($collection->getItems() as $item){
                    $ret .= '<div style="font-size: 90%; margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">' . $item->getName() . '</div>';
                }
            }

            $result['items'][$idx][$column] = $ret;
            $idx++;
        }
    }

    public function afterGetData(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        $result
    ){

        $columns = $this->_getVisibleColumns($subject->getConfigData());

        foreach($columns as $column){
            switch ($column){
                case "amasty_categories":
                    $this->_initCategories($subject->getCollection(), $result);
                    break;
                case "amasty_related_products":
                case "amasty_up_sells":
                case "amasty_cross_sells":
                    $this->_initRelatedProducts($subject->getCollection(), $column, $result);
                    break;
                default:
                    if (isset($result['items'])) {
                        foreach ($result['items'] as $idx => $item) {
                            $this->_initExtra($result['items'][$idx], $column);
                        }
                    }
                    break;
                case "price":
                    if (isset($result['items'])) {
                        foreach ($result['items'] as $idx => $item) {
                            if (isset($item['price'])){
                                $result['items'][$idx]['amasty_price'] = $item['price'];
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

}
