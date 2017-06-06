<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Automation;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Name
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Automation
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context, UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

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
     * @param $entityId
     * @param $name
     * @return string
     */
    protected function getLink($entityId, $name)
    {
        $url = $this->context->getUrl('aw_helpdesk/automation/edit', ['id' => $entityId]);
        return '<a href="' . $url . '">' . $name . '</a>';
    }
}