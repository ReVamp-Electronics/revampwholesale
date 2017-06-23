<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group name
     *
     * @var string
     */
    protected $_blockGroup = 'Amasty_CustomerAttributes';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('entity_attribute')->getId()) {
            $frontendLabel = $this->coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Customer Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Customer Attribute');
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('amcustomerattr/*/validate', ['_current' => true]);
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'amcustomerattr/attribute/save',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_customer_attribute';

        parent::_construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->removeButton('back');
                $this->addButton(
                    'close',
                    [
                        'label' => __('Close Window'),
                        'class' => 'cancel',
                        'onclick' => 'window.close()',
                        'level' => -1
                    ]
                );
            }
        } else {
            $this->addButton(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ]
            );
        }
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->buttonList->update('save', 'class', 'save primary');
        $this->buttonList->update(
            'save',
            'data_attribute',
            ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );

        $entityAttribute = $this->coreRegistry->registry('entity_attribute');
        if (!$entityAttribute || !$entityAttribute->getIsUserDefined()) {
            $this->buttonList->remove('delete');
        } else {
            $this->buttonList->update('delete', 'label', __('Delete Attribute'));
        }

        if ($entityAttribute && $entityAttribute->getTypeInternal() == 'selectgroup') {
            $this->addButton(
                'update_button',
                [
                    'label' => __('Update Options'),
                    'class' => 'save',
                    'on_click' => 'setLocation(\'' . $this->getUpdateUrl() . '\')',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'UpdateEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    public function getUpdateUrl()
    {
        return $this->getUrl(
            'amcustomerattr/attribute/update',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }
}
