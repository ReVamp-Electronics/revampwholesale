<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department;

/**
 * Class Gateway
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department
 * @codeCoverageIgnore
 */
class Gateway extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['gateway_email']) && $item['gateway_email']) {
                continue;
            }
            $item['gateway_email'] = __('No');
        }

        return $dataSource;
    }
}
