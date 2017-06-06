<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Status
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Ticket statuses source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        array $data = []
    ) {
        $this->statusSource = $statusSource;
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
            $item['status'] = $this->prepareContent($item['status']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $statusCode
     * @return string
     */
    protected function prepareContent($statusCode)
    {
        return
            "<span class='{$statusCode}' >" .
            $this->statusSource->getOptionLabelByValue($statusCode)->render()
            . "</span>";
    }
}