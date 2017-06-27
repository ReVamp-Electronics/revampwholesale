<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Shipping;

use IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction;
use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Shipping
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_shipping';

    /**
     * {@inheritdoc}
     */
    protected function update()
    {
        $this->updateShippingMethod();
    }

    /**
     * @return void
     */
    private function updateShippingMethod()
    {
        $params = $this->prepareParams();
        $this->shipping->initParams($params);

        Logger::getInstance()->addMessageForLevel(
            'shipping_info',
            'Shipping information has been changed'
        );

        $this->shipping->updateShippingMethod();
    }

    /**
     * @return string
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function prepareParams()
    {
        $params = [
            'shipping_method',
            'order_id',
            'price_excl_tax',
            'price_incl_tax',
            'tax_percent',
            'description'
        ];

        foreach ($params as $param) {
            $val = $this->getRequest()->getParam($param, null);
            if ($val == null) {
                throw new LocalizedException(__('Empty param ' . $param));
            }
            $params[$param] = $val;
        }
        return $params;
    }

    /**
     * @return bool
     */
    protected function needUpdateShippingInfo()
    {
        return false;
    }
}
