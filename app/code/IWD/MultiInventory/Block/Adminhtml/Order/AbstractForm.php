<?php

namespace IWD\MultiInventory\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;

/**
 * Class AbstractForm
 * @package IWD\MultiInventory\Block\Adminhtml\Order
 */
class AbstractForm extends Template
{
    /**
     * @return string
     */
    public function getActionsForm()
    {
        /** @var \IWD\MultiInventory\Block\Adminhtml\Order\View\Actions $actionsForm */
        $actionsForm = $this->getChildBlock('iwdordermamager_order_actions');
        if ($actionsForm) {
            $blockId = $actionsForm->getBlockId();
            $actionsForm->setBlockId($blockId);

            return $actionsForm->toHtml();
        }

        return '';
    }
}
