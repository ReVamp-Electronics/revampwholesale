<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Controller\Adminhtml\Relations;

/**
 * Currency Switcher REFRESH controller
 */
class Refresh extends \Magento\Framework\App\Action\Action
{

    /**
     * Refresh(update) Relations action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_objectManager->create('\MageWorx\CurrencySwitcher\Model\Relations')->refreshRelations();

            $this->messageManager->addSuccessMessage(__('Currency relations were saved successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
    }
}
