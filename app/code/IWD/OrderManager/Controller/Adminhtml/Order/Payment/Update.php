<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Payment;

use IWD\OrderManager\Controller\Adminhtml\Order\Additional\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Payment
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_payment';

    /**
     * @return void
     */
    protected function update()
    {
        $params = $this->prepareParams();
        $orderId = $this->getOrderId();

        $this->payment->setPaymentData($params);

        $this->payment->setOrderId($orderId);
        $this->payment->editPaymentMethod();
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
    private function prepareParams()
    {
        $payment = $this->getRequest()->getParam('payment', null);
        if (empty($payment)) {
            throw new LocalizedException(__('Empty payment params'));
        }

        return $payment;
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
    protected function needUpdatePaymentInfo()
    {
        return false;
    }
}
