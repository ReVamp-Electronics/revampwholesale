<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Address;

use Magento\Backend\Block\Template;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Address
 */
class Form extends Template
{
    /**
     * @var string
     */
    private $addressType = '';

    /**
     * @param string $addressType
     * @return $this
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressForm()
    {
        $addressForm = $this->getChildBlock(
            'iwdordermamager_order_address_form'
        );

        if ($addressForm) {
            $formBlock = $addressForm->getChildBlock('form');
            if (empty($formBlock)) {
                return '';
            }

            $formBlock->setDisplayVatValidationButton(false);

            $form = $formBlock->getForm();

            if (!empty($this->addressType)) {
                $form->addFieldNameSuffix($this->addressType . '_address');
                $form->setHtmlNamePrefix($this->addressType . '_address');
                $form->setHtmlIdPrefix($this->addressType . '_address_');
                $form->setId($this->addressType . '_address_edit_form');
            }

            return $form->toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getActionsForm()
    {
        $actionsForm = $this->getChildBlock('iwdordermamager_order_actions');
        if ($actionsForm) {
            $blockId = $actionsForm->getBlockId() . '-' . $this->addressType;
            $actionsForm->setBlockId($blockId);

            return $actionsForm->toHtml();
        }

        return '';
    }
}
