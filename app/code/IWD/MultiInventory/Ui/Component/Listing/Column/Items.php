<?php

namespace IWD\MultiInventory\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Items
 * @package IWD\MultiInventory\Ui\Component\Listing\Column
 */
class Items extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orderedItems = isset($item['iwd_order_items_name'])
                    ? explode(',', $item['iwd_order_items_name'])
                    : [];

                $item['iwd_order_items'] = !empty($orderedItems)
                    ? '<ul><li>' . implode('</li><li>', $orderedItems) . '</li></ul>'
                    : '';
            }
        }

        return $dataSource;
    }
}
