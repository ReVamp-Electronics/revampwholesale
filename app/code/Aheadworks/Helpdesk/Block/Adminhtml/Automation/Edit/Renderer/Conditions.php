<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Conditions
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer
 */
class Conditions extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::automation/edit/conditions.phtml';

    /**
     * Condition source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Condition
     */
    protected $conditionSource;

    /**
     * Operator source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Operator
     */
    protected $operatorSource;

    /**
     * Value source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Values
     */
    protected $valuesSource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Condition $conditionSource
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Operator $operatorSource
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Values $valuesSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Helpdesk\Model\Source\Automation\Condition $conditionSource,
        \Aheadworks\Helpdesk\Model\Source\Automation\Operator $operatorSource,
        \Aheadworks\Helpdesk\Model\Source\Automation\Values $valuesSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->conditionSource = $conditionSource;
        $this->operatorSource = $operatorSource;
        $this->valuesSource = $valuesSource;
    }

    /**
     * Get objects by event type
     * @param $eventType
     * @return array
     */
    public function getObjectVariablesByEventType($eventType)
    {
        $allVariables = $this->conditionSource->getAvailableOptionByEventType();
        $result = [];
        if (array_key_exists($eventType, $allVariables)) {
            $result = $allVariables[$eventType];
        }
        return $result;
    }

    /**
     * Get operators for condition type
     * @param $conditionType
     * @return array
     */
    public function getOperatorsForConditionType($conditionType)
    {
        $allVariables = $this->operatorSource->getAvailableOptionByConditionType();
        $result = [];
        if (array_key_exists($conditionType, $allVariables)) {
            $result = $allVariables[$conditionType];
        }
        return $result;
    }

    /**
     * Get values for condition type
     * @param $conditionType
     * @return array
     */
    public function getValuesForConditionType($conditionType)
    {
        $allVariables = $this->valuesSource->getAvailableOptionByConditionType();
        $result = [];
        if (array_key_exists($conditionType, $allVariables)) {
            $result = $allVariables[$conditionType];
        }
        return $result;
    }

    /**
     * Get default object for event
     * @param $eventType
     * @return mixed
     */
    public function getDefaultObjectByEventType($eventType)
    {
        $availableVariables = $this->getObjectVariablesByEventType($eventType);
        return key($availableVariables);
    }

    /**
     * Get all values as json
     * @return string
     */
    public function getJsonAvailableValues()
    {
        return json_encode($this->valuesSource->getAvailableOptionByConditionType());
    }

    /**
     * Get all operators as json
     * @return string
     */
    public function getJsonAvailableOperators()
    {
        return json_encode($this->operatorSource->getAvailableOptionByConditionType());
    }

    /**
     * Get all objects as json
     * @return string
     */
    public function getJsonAvailableObjects()
    {
        return json_encode($this->conditionSource->getAvailableOptionByEventType());
    }

    /**
     * Get class for select option
     * @param $key
     * @param $value
     * @return string
     */
    public function getSelected($key, $value) {
        $result = '';
        if (is_array($value) && false !== array_search($key, $value)) {
            $result = 'selected';
        } elseif ($key == $value) {
            $result = 'selected';
        }

        return $result;
    }
}