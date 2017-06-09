<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Additional;

/**
 * Class Payment
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Additional
 */
class Payment extends AbstractAction
{
    /**
     * @return void
     */
    protected function update()
    {
        //TODO: add logic
    }

    /**
     * @return string
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
