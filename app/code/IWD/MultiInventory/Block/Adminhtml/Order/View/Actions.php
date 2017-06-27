<?php

namespace IWD\MultiInventory\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;

/**
 * Class Actions
 * @package IWD\MultiInventory\Block\Adminhtml\Order\View
 */
class Actions extends Template
{
    /**
     * @param string $action
     * @return bool
     */
    public function isAllowedAction($action)
    {
        return $this->_authorization->isAllowed('IWD_MultiInventory::iwdordermanager_' . $action);
    }

    /**
     * Example of return value:
     * [
     *    'title' => "Confirmation",
     *    'type'  => "checkbox",
     *    'id'    => "confirm",
     *    'class' => "scalable primary update action-default"
     * ]
     * @return string[]
     */
    public function getDropdownMenu()
    {
        return $this->getDropdownActions();
    }
}
