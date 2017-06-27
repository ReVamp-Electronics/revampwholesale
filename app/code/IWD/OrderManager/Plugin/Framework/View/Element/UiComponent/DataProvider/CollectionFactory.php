<?php

namespace IWD\OrderManager\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as MagentoCollectionFactory;

/**
 * Class CollectionFactory
 * @package IWD\OrderManager\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class CollectionFactory
{
    /**
     * @param MagentoCollectionFactory $subject
     * @param \Closure $proceed
     * @param $requestName
     * @return mixed
     */
    public function aroundGetReport(
        MagentoCollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);
        return $result; //TODO: add logic

        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection) {
                /**
                 * @var $result \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
                 */
                $result->getSelect()
                    ->join(
                        ['order_items' => $result->getTable("sales_order_item")],
                        'main_table.entity_id=order_items.order_id',
                        [
                            'iwd_order_items_name' => new \Zend_Db_Expr('group_concat(DISTINCT order_items.name SEPARATOR ", ")')
                        ]
                    )->group(
                        'main_table.entity_id'
                    );
            }
        }
        return $result;
    }
}
