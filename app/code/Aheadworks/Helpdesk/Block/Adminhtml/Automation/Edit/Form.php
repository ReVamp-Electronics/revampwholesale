<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit;

/**
 * Class Form
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Event source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Event
     */
    protected $eventSource;

    /**
     * Status source
     * @var \Aheadworks\Helpdesk\Model\Source\Automation\Status
     */
    protected $statusSource;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Event $eventSource
     * @param \Aheadworks\Helpdesk\Model\Source\Automation\Status $statusSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Helpdesk\Model\Source\Automation\Event $eventSource,
        \Aheadworks\Helpdesk\Model\Source\Automation\Status $statusSource,
        array $data = []
    ) {
        $this->eventSource = $eventSource;
        $this->statusSource = $statusSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Aheadworks\Popup\Model\Popup */
        $model = $this->_coreRegistry->registry('aw_helpdesk_automation');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('automation_');

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __(''),
            ]
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => $this->statusSource->getOptionArray()
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'class' => 'validate-zero-or-greater validate-number validate-digits'
            ]
        );

        $fieldset->addField(
            'event',
            'select',
            [
                'name' => 'event',
                'label' => __('Event'),
                'title' => __('Event'),
                'options' => $this->eventSource->getOptionArray()
            ]
        );


        $defaultEvent = $this->eventSource->getOptionArray();
        $defaultEvent = key($defaultEvent);

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __('Conditions'),
            ]
        );

        $conditionFormHtml = $this->getLayout()->createBlock(
            'Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer\Conditions'
        )
            ->setConditions(($model->getConditions()) ? $model->getConditions(): [])
            ->setEventType(($model->getEvent())? $model->getEvent(): $defaultEvent)
            ->toHtml();

        $fieldset->addField(
            'conditions',
            'Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Form\Element\Template',
            [
                'name' => 'conditions',
                'content' => $conditionFormHtml
            ]
        );

        $addConditionHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Template'
        )
            ->setTemplate('Aheadworks_Helpdesk::automation/edit/conditions/add.phtml')
            ->setItemsCount(count($model->getConditions()))
            ->toHtml();

        $fieldset->addField(
            'add_condition',
            'note',
            [
                'name' => 'add_condition',
                'text' => __(''),
                'after_element_html' => $addConditionHtml
            ]
        );

        $fieldset = $form->addFieldset(
            'actions_fieldset',
            [
                'legend' => __('Actions'),
            ]
        );

        $actionFormHtml = $this->getLayout()->createBlock(
            'Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Renderer\Actions'
        )
            ->setActions(($model->getActions()) ? $model->getActions(): [])
            ->setEventType(($model->getEvent())? $model->getEvent(): $defaultEvent)
            ->toHtml();
        $fieldset->addField(
            'actions',
            'Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Form\Element\Template',
            [
                'name' => 'actions',
                'content' => $actionFormHtml
            ]
        );

        $addActionHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Template'
        )
            ->setTemplate('Aheadworks_Helpdesk::automation/edit/actions/add.phtml')
            ->setItemsCount(count($model->getActions()))
            ->toHtml();

        $fieldset->addField(
            'add_action',
            'note',
            [
                'name' => 'add_action',
                'text' => __(''),
                'after_element_html' => $addActionHtml
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
