<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\System\Config\Form\Fieldset\General;

/**
 * Class Agent
 * @package Aheadworks\Helpdesk\Block\Adminhtml\System\Config\Form\Filedset\General
 */
class Agent extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Agent source
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    protected $agentSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->agentSource = $agentSource;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $value = $element->getValue();

        if (null === $value) {
            $allValues = $this->agentSource->getOptionArray();
            $value = array_keys($allValues);
            $element->setValue($value);
        }
        return parent::_getElementHtml($element);
    }
}
