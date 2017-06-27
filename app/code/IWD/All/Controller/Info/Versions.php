<?php

namespace IWD\All\Controller\Info;

/**
 * Class Versions
 * @package IWD\All\Controller\Info
 */
class Versions extends \Magento\Contact\Controller\Index
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
