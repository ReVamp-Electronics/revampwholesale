<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CurrencySwitcher\Controller\Adminhtml\Relations;

/**
 * Currency Switcher controller
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * View Relations action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('MageWorx_CurrencySwitcher::system_currency_relations');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Currency Relations'));
        $this->_addContent(
            $this->_view->getLayout()->createBlock('MageWorx\CurrencySwitcher\Block\Adminhtml\Currency\Relations')
        );
        $this->_view->renderLayout();
    }
}
