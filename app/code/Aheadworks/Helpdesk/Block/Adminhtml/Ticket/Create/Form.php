<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Create;

/**
 * Class Form
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Create
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Ticket statuses source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Ticket priorities source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Priority
     */
    protected $prioritySource;

    /**
     * Agents source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    protected $agentSource;

    /**
     * Ticket orders source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Order
     */
    protected $orderSource;

    /**
     * Departments source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Department
     */
    protected $departmentSource;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Renderer attachment
     * @var Renderer\Attachment
     */
    protected $renderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Order $orderSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentSource
     * @param Renderer\Attachment $renderer
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Order $orderSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentSource,
        \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Create\Renderer\Attachment $renderer,
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        $this->prioritySource = $prioritySource;
        $this->agentSource = $agentSource;
        $this->orderSource = $orderSource;
        $this->departmentSource = $departmentSource;
        $this->systemStore = $systemStore;
        $this->renderer = $renderer;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('ticket_');

        /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __(''),
            ]
        );

        $fieldset->addField(
            'subject',
            'text',
            [
                'name' => 'subject',
                'label' => __('Subject'),
                'title' => __('Subject'),
                'required' => true
            ]
        );

        $departmentOptions = $this->departmentSource->getAvailableOptionsForUpdate();
        $fieldset->addField(
            'department_id',
            'select',
            [
                'name' => 'department_id',
                'label' => __('Department'),
                'title' => __('Department'),
                'values' => $departmentOptions,
                'required' => true
            ]
        );

        $fieldset->addField(
            'customer_email',
            'text',
            [
                'name' => 'customer_email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'required' => true,
                'after_element_html' => $this->getAutocompleteScriptHtml()
            ]
        );

        $fieldset->addField(
            'customer_name',
            'text',
            [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'required' => true
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'label'    => __('Store View'),
                    'name'     => 'store_id',
                    'type'     => 'store',
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, false),
                ]
            );
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
        }

        $fieldset->addField(
            'order_id',
            'select',
            [
                'name' => 'order_id',
                'label' => __('Order'),
                'title' => __('Order'),
                'options' => [],
            ]
        );

        foreach ($departmentOptions as $departmentId => $departmentLabel) {
            $agentsOptions = $this->agentSource->getAvailableOptionsForDepartment($departmentId);
            $fieldset->addField(
                'agent_id_dep' . $departmentId,
                'select',
                [
                    'name' => 'agent_id',
                    'label' => __('Agent'),
                    'title' => __('Agent'),
                    'values' =>  $agentsOptions,
                ]
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->statusSource->getFormOptionArray(),
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::DEFAULT_STATUS
            ]
        );

        $fieldset->addField(
            'priority',
            'select',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'values' => $this->prioritySource->getOptionArray(),
                'value' => \Aheadworks\Helpdesk\Model\Source\Ticket\Priority::DEFAULT_VALUE
            ]
        );

        $fieldset->addField(
            'content',
            'textarea',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'required' => true
            ]
        );

        $attachmentHtml = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Template'
        )
            ->setTemplate('Aheadworks_Helpdesk::ticket/create/attachment.phtml')
            ->setFileUploadUrl($this->getUrl('*/*/upload'))
            ->toHtml();

        $fieldset->addField(
            'attachments',
            'label',
            [
                'name' => 'attachments',
                'label' => __('Attach File(s)'),
                'title' => __('Attach File(s)'),
                'after_element_html' => $attachmentHtml
            ]
        );

        $fieldset->addField(
            'cc_recipients',
            'text',
            [
                'name' => 'cc_recipients',
                'label' => __('CC Recipients'),
                'title' => __('CC Recipients'),
            ]
        );
        $formData = $this->_backendSession->getFormData();
        if ($formData) {
            $form->setValues($formData);
            $this->_backendSession->setFormData(false);
        }

        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $dependencyBlock = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Element\Dependence'
        )->addFieldMap(
            "{$htmlIdPrefix}department_id",
            'department_id'
        );

        foreach ($departmentOptions as $departmentId => $departmentLabel) {
            $dependencyBlock->addFieldMap(
                "{$htmlIdPrefix}agent_id_dep" . $departmentId,
                'dep' . $departmentId
            )->addFieldDependence(
                'dep' . $departmentId,
                'department_id',
                $departmentId
            );
        }
        $this->setChild(
            'form_after',
            $dependencyBlock
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get autocomplete script
     * @return string
     */
    protected function getAutocompleteScriptHtml()
    {
        $template =  "
            <script type='text/javascript'>
                require(['jquery','AWHelpdesk_Autocomplete'], function(){
                    jQuery('.admin__field.field.field-order_id').hide();
                    jQuery('#ticket_customer_email').on('input', function(){
                        jQuery('.admin__field.field.field-order_id').hide();
                    });
                    var emailFieldSelector = '#ticket_general_fieldset .admin__field.field.field-customer_email';
                    jQuery(emailFieldSelector).append('<div class=\"loader\"></div>');
                    var emailFieldSelector = '#ticket_general_fieldset .admin__field.field.field-customer_email .loader';
                    jQuery('#ticket_customer_email').autocomplete({
                        serviceUrl: '{$this->getUrl('*/autocomplete/customers')}',
                        minChars: 3,
                        onSearchStart : function(){ jQuery(emailFieldSelector).show() },
                        onSearchComplete: function(){ jQuery(emailFieldSelector).hide() },
                        onSelect: function(suggestion) {
                            jQuery('#ticket_customer_name').val(suggestion.customer_name);
                            var storeId = jQuery('#ticket_store_id').val();
                            awHelpdeskUpdateOrders(suggestion.value, storeId);
                        }
                    });

                    function awHelpdeskUpdateOrders(customerEmail, storeId)
                    {
                        jQuery.ajax({
                            url: '{$this->getUrl('*/autocomplete/orders')}',
                            type: 'POST',
                            dataType: 'json',
                            context: this,
                            async: true,
                            data: {
                                isAjax: 'true',
                                customer_email: customerEmail,
                                store_id: storeId
                            },
                            showLoader: false,
                            complete: function(response) {
                                try {
                                    eval('var json = ' + response.responseText + ' || {}');
                                } catch (e) {
                                    return false;
                                }
                                if (json.options) {
                                    jQuery('#ticket_order_id').html(json.options);
                                    jQuery('.admin__field.field.field-order_id').show();
                                }
                            }
                        });
                    }
                });
            </script>";

        return $template;
    }
}
