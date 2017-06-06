<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Helpdesk\Model\Source\Websites as WebsitesSource;

/**
 * Class Websites
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns\Department
 */
class Websites extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var WebsitesSource
     */
    private $websitesSource;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param WebsitesSource $websitesSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        WebsitesSource $websitesSource,
        array $components = [],
        array $data = []
    ) {
        $this->websitesSource = $websitesSource;
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
            foreach ($item['website_ids'] as &$website) {
                $website = $this->websitesSource->getOptionByValue($website);
            }
        }

        return $dataSource;
    }
}
