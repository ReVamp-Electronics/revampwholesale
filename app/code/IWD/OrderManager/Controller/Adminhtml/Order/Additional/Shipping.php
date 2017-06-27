<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Additional;

/**
 * Class Shipping
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Additional
 */
class Shipping extends AbstractAction
{
    /**
     * @return void
     * @throws \Exception
     */
    protected function update()
    {
        $params = $this->getRequest()->getParams();
        $orderId = $this->getOrderId();

        $this->shipping->setOrderId($orderId);
        $this->shipping->initParams($params);
        $this->shipping->updateShippingMethod();
        $this->shipping->recollectShippingAmount();
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
    protected function needUpdateShippingInfo()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
