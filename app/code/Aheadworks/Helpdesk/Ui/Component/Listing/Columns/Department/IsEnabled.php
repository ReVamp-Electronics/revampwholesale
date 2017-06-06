<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Helpdesk\Model\Source\YesNo as YesNoSource;

/**
 * Class IsEnabled
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department
 * @codeCoverageIgnore
 */
class IsEnabled extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var YesNoSource
     */
    private $yesNoSource;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param YesNoSource $yesNoSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        YesNoSource $yesNoSource,
        array $components = [],
        array $data = []
    ) {
        $this->yesNoSource = $yesNoSource;
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
            $item['is_enabled'] = $this->yesNoSource->getOptionByValue($item['is_enabled']);
        }

        return $dataSource;
    }
}
