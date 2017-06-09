<?php

namespace IWD\SalesRep\Block\Adminhtml\Commission\Edit;

use \IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class Form
 * @package IWD\SalesRep\Block\Adminhtml\Commission\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /**
         * @var $attachedCustomerModel AttachedCustomer
         * @var \Magento\Framework\Data\Form $form
         */
        $attachedCustomerModel = $this->_coreRegistry->registry('attached_customer');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'salesrep_commission_form',
                    'class' => 'iwdsr-edit-commission',
                    'action' => $this->getUrl('salesrep/user/commissionPost'),
                    'method' => 'post',
                    'use_container' => true,
                ]
            ]
        );

        $form->addField(
            'customer_id',
            'hidden',
            [
                'name' => 'customer_id',
                'value' => $attachedCustomerModel->getCustomerId(),
            ]
        );

        $form->addField(
            'salesrep_id',
            'hidden',
            [
                'name' => 'salesrep_id',
                'value' => $attachedCustomerModel->getSalesrepId(),
            ]
        );

        $form->addField(
            AttachedCustomer::COMMISSION_TYPE,
            'select',
            [
                'name' => AttachedCustomer::COMMISSION_TYPE,
                'label' => 'Commission Type',
                'options' => $attachedCustomerModel->getCommissionTypeOptions(),
                'value' => $attachedCustomerModel->getData(AttachedCustomer::COMMISSION_TYPE)
            ]
        );

        $form->addField(
            AttachedCustomer::COMMISSION_RATE,
            '\IWD\SalesRep\Block\Data\Form\Element\Number',
            [
                'name' => AttachedCustomer::COMMISSION_RATE,
                'label' => 'Rate per Order',
                'value' => $attachedCustomerModel->getData(AttachedCustomer::COMMISSION_RATE),
                'min' => 0,
                'step' => 0.01,
            ]
        );

        $form->addField(
            AttachedCustomer::COMMISSION_APPLY_WHEN . '_hidden',
            'hidden',
            [
                'name' => AttachedCustomer::COMMISSION_APPLY_WHEN,
                'value' => AttachedCustomer::COMMISSION_APPLY_AFTER,
            ]
        );

        $form->addField(
            AttachedCustomer::COMMISSION_APPLY_WHEN,
            'select',
            [
                'label' => 'Apply Commission Before / After Discounts',
                'name' => AttachedCustomer::COMMISSION_APPLY_WHEN,
                'options' => $attachedCustomerModel->getCommissionApplyWhenOptions(),
                'value' => $attachedCustomerModel->getData(AttachedCustomer::COMMISSION_APPLY_WHEN),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
