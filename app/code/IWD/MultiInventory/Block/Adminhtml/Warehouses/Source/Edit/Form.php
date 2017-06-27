<?php

namespace IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class Form
 * @package IWD\MultiInventory\Block\Adminhtml\Warehouses\Source\Edit
 */
class Form extends Generic
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('warehouses_source_form');
        $this->setTitle(__('Department Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
