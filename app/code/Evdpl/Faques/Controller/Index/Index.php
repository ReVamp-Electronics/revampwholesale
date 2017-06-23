<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Evdpl\Faques\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Show Contact Us page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('faques');
        $this->_view->renderLayout();
    }
}
