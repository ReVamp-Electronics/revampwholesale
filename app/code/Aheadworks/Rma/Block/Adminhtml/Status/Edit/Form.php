<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Status\Edit;

/**
 * Class Form
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Aheadworks\Rma\Model\Source\Email\Template\Customer
     */
    protected $templatesToCustomer;

    /**
     * @var \Aheadworks\Rma\Model\Source\Email\Template\Admin
     */
    protected $templatesToAdmin;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Aheadworks\Rma\Model\Source\Email\Template\Customer $templatesToCustomer
     * @param \Aheadworks\Rma\Model\Source\Email\Template\Admin $templatesToAdmin
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Rma\Model\Source\Email\Template\Customer $templatesToCustomer,
        \Aheadworks\Rma\Model\Source\Email\Template\Admin $templatesToAdmin,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->templatesToCustomer = $templatesToCustomer;
        $this->templatesToAdmin = $templatesToAdmin;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('aw_rma_status');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'id' => 'edit_form',
                        'class' => 'aw-rma-admin-edit-form status',
                        'action' => $this->getData('action'),
                        'method' => 'post'
                    ]
            ]
        );

        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('status_');

        $fieldSet = $form->addFieldset('general_fieldset', ['legend' => __('General Information')]);
        $fieldSet->addField('id', 'hidden', ['name' => 'id']);
        $fieldSet->addField(
            'name',
            'label',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name')
            ]
        );

        $fieldSet = $form->addFieldset('frontend_labels_fieldset', ['legend' => __('Frontend Labels')]);
        foreach ($this->_storeManager->getStores() as $store) {
            $fieldSet->addField(
                'attribute-frontend_label_' . $store->getId(),
                'Aheadworks\Rma\Block\Adminhtml\Form\Element\FrontendLabel',
                [
                    'name' => "attribute[frontend_label][" . $store->getId() . "]",
                    'store_id' => $store->getId(),
                    'required'  => true,
                ]
            );
        }

        $fieldSet = $form->addFieldset('templates_fieldset', ['legend' => __('Templates')]);
        $fieldSet->addField(
            'is_email_customer',
            'checkbox',
            [
                'name' => 'is_email_customer',
                'checked' => $model->getIsEmailCustomer(),
                'label' => __('Email to Customer'),
                'title' => __('Email to Customer'),
                'after_element_js' => $this->getTemplateCheckboxJs('is_email_customer')
            ]
        );
        foreach ($this->_storeManager->getStores() as $store) {
            $fieldSet->addField(
                'attribute-template_to_customer_' . $store->getId(),
                'Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element\EmailTemplate',
                [
                    'name' => "attribute[template_to_customer][" . $store->getId() . "]",
                    'store_id' => $store->getId(),
                    'options' => $this->templatesToCustomer->setPath('aw_rma_email_template_to_customer_status_' . $model->getId())->getOptions(),
                    'class' => 'status-template-select',
                    'field_extra_attributes' => 'data-visible=is_email_customer',
                    'to_admin' => false
                ]
            );
        }
        $fieldSet->addField(
            'is_email_admin',
            'checkbox',
            [
                'name' => 'is_email_admin',
                'checked' => $model->getIsEmailAdmin(),
                'label' => __('Email to Admin'),
                'title' => __('Email to Admin'),
                'after_element_js' => $this->getTemplateCheckboxJs('is_email_admin')
            ]
        );
        foreach ($this->_storeManager->getStores() as $store) {
            $fieldSet->addField(
                'attribute-template_to_admin_' . $store->getId(),
                'Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element\EmailTemplate',
                [
                    'name' => "attribute[template_to_admin][" . $store->getId() . "]",
                    'store_id' => $store->getId(),
                    'options' => $this->templatesToAdmin->setPath('aw_rma_email_template_to_admin_status_' . $model->getId())->getOptions(),
                    'class' => 'status-template-select',
                    'field_extra_attributes' => 'data-visible=is_email_admin',
                    'to_admin' => true
                ]
            );
        }
        $fieldSet->addField(
            'is_thread',
            'checkbox',
            [
                'name' => 'is_thread',
                'checked' => $model->getIsThread(),
                'label' => __('Message to Request Thread'),
                'title' => __('Message to Request Thread'),
                'after_element_js' => $this->getTemplateCheckboxJs('is_thread')
            ]
        );
        foreach ($this->_storeManager->getStores() as $store) {
            $fieldSet->addField(
                'attribute-template_to_thread_' . $store->getId(),
                'Aheadworks\Rma\Block\Adminhtml\Status\Edit\Form\Element\ThreadTemplate',
                [
                    'name' => "attribute[template_to_thread][" . $store->getId() . "]",
                    'store_id' => $store->getId(),
                    'required'  => true,
                    'field_extra_attributes' => 'data-visible=is_thread'
                ]
            );
        }

        $form->setValues($this->prepareFormValues($model));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Aheadworks\Rma\Model\Status $model
     * @return array
     */
    protected function prepareFormValues($model)
    {
        $formValues = $model->getData();
        $formValues['is_email_customer'] = 1;
        $formValues['is_email_admin'] = 1;
        $formValues['is_thread'] = 1;
        foreach ($model->getAttribute() as $attrCode => $attrValue) {
            foreach ($attrValue as $storeId => $value) {
                $formValues['attribute-' . $attrCode . '_' . $storeId] = $value;
            }
        }
        return $formValues;
    }

    /**
     * @param string $id
     * @return string
     */
    protected function getTemplateCheckboxJs($id)
    {
        return <<<HTML
    <script>
        require(['jquery', 'awRmaStatusFormCheckbox'], function($, templateCheckbox){
            $(document).ready(function() {
                templateCheckbox({}, $('#status_{$id}'));
            });
        });
    </script>
HTML;
    }
}
