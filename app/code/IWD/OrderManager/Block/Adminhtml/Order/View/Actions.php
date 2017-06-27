<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\View;

use \Magento\Backend\Block\Template;

class Actions extends Template
{
    /**
     * @param string $action
     * @return bool
     */
    public function isAllowedAction($action)
    {
        return $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_' . $action);
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
