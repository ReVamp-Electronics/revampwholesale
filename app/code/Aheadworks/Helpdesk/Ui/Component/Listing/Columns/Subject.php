<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Subject
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns
 */
class Subject extends \Magento\Ui\Component\Listing\Columns\Column
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
            $item['subject'] = $this->getLink($item['ticket_id'], $item['subject']);
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
        $url = $this->context->getUrl('aw_helpdesk/ticket/edit', ['id' => $entityId]);
        return '<a href="' . $url . '">' . htmlspecialchars($name) . '</a>';
    }
}