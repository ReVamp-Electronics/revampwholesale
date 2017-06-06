<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Actions
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer
 */
class Actions extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::automation/edit/actions.phtml';

    /**
     * Action source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Action
     */
    protected $actionSource;

    /**
     * Value source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Values
     */
    protected $valuesSource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Action $actionSource
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Values $valuesSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Helpdesk\Model\Source\Automation\Action $actionSource,
        \Aheadworks\Helpdesk\Model\Source\Automation\Values $valuesSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->actionSource = $actionSource;
        $this->valuesSource = $valuesSource;
    }

    /**
     * Get actions for event type
     * @param $eventType
     * @return array
     */
    public function getActionVariablesByEventType($eventType)
    {
        $allVariables = $this->actionSource->getAvailableOptionByEventType();
        $result = [];

        if (array_key_exists($eventType, $allVariables)) {
            $result = $allVariables[$eventType];
        }
        return $result;
    }

    /**
     * Get values for action type
     * @param $actionType
     * @return array
     */
    public function getValuesForActionType($actionType)
    {
        $allVariables = $this->valuesSource->getAvailableOptionByActionType();
        $result = [];
        if (array_key_exists($actionType, $allVariables)) {
            $result = $allVariables[$actionType];
        }
        return $result;
    }

    /**
     * Get default action for event type
     * @param $eventType
     * @return mixed
     */
    public function getDefaultActionByEventType($eventType)
    {
        $availableVariables = $this->getActionVariablesByEventType($eventType);
        return key($availableVariables);
    }

    /**
     * Get all values as json
     * @return string
     */
    public function getJsonAvailableValues()
    {
        return json_encode($this->valuesSource->getAvailableOptionByActionType());
    }

    /**
     * Get all actions as json
     * @return string
     */
    public function getJsonAvailableObjects()
    {
        return json_encode($this->actionSource->getAvailableOptionByEventType());
    }
}