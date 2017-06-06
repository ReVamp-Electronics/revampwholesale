<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Priority
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Priority extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Ticket priorities source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Priority
     */
    protected $prioritySource;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource,
        array $data = []
    ) {
        $this->prioritySource = $prioritySource;
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
            $item['priority'] = $this->prepareContent($item['priority']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $priorityCode
     * @return string
     */
    protected function prepareContent($priorityCode)
    {
        return
            "<span class='{$priorityCode}' >" .
            $this->prioritySource->getOptionLabelByValue($priorityCode)->render()
            . "</span>";
    }
}