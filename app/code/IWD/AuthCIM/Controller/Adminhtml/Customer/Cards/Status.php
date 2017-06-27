<?php

namespace IWD\AuthCIM\Controller\Adminhtml\Customer\Cards;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Status
 * @package IWD\AuthCIM\Controller\Adminhtml\Customer\Cards
 */
class Status extends AbstractAction
{
    /**
     * @return array
     */
    public function action()
    {
        $hash = $this->getCardHash();
        $status = $this->getStatus();

        $this->getCardModel()->statusPaymentProfile($hash, $status);

        return ['error' => false, 'status' => true];
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    private function getStatus()
    {
        $status = $this->getRequest()->getParam('status', null);
        if (empty($status)) {
            throw new LocalizedException(__('Status is empty'));
        }

        return $status;
    }
}
