<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department;

/**
 * Class Name
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department
 * @codeCoverageIgnore
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
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
            $item['name'] = $this->getLink($item['id'], $item['name']);
        }

        return $dataSource;
    }

    /**
     * Get link for name
     *
     * @param int $entityId
     * @param string $name
     * @return string
     */
    private function getLink($entityId, $name)
    {
        $url = $this->context->getUrl('aw_helpdesk/department/edit', ['id' => $entityId]);
        return '<a href="' . $url . '">' . htmlspecialchars($name) . '</a>';
    }
}
