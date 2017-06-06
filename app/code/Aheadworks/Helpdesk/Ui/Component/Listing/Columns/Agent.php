<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Agent
 * @package Aheadworks\Popup\Ui\Component\Listing\Columns
 */
class Agent extends \Magento\Ui\Component\Listing\Columns\Column
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
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\User\Model\UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        $this->agentFactory = $userFactory;
        $this->agentResource = $userResource;
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
            $item['agent_name'] = $this->prepareContent($item['agent_name'], $item['agent_id']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $agentName
     * @param $agentId
     * @return string
     */
    protected function prepareContent($agentName, $agentId)
    {
        $html = $agentName;
        $agent = $this->agentFactory->create();
        $this->agentResource->load($agent, $agentId);
        if (!$agent || !$agent->getId()) {
            $html = "<span class='unassigned' >" .
                $agentName
                . "</span>";
        }
        return $html;
    }
}