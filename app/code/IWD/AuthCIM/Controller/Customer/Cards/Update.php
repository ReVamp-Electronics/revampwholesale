<?php

namespace IWD\AuthCIM\Controller\Customer\Cards;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class Delete
 * @package IWD\AuthCIM\Controller\Customer\Cards
 */
class Update extends AbstractAction
{
    /**
     * @return array
     */
    public function action()
    {
        $hash = $this->getCardHash();
        $customerId = $this->getCustomerId();
        $address = $this->getAddressData();
        $payment = $this->getPaymentData();

        $this->getCoreRegistry()->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

        if ($hash == "0") {
            $this->getCardModel()->addPaymentProfile($customerId, $address, $payment);
        } else {
            $this->getCardModel()->updatePaymentProfile($hash, $address, $payment);
        }

        return ['error' => false, 'status' => true, 'list_html' => $this->getResultHtml()];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPaymentData()
    {
        $payment = parent::getPaymentData();
        $payment['cc_save'] = 1;

        return $payment;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getResultHtml()
    {
        $resultPage = $this->getResultPageFactory()->create();

        $infoFormContainer = $resultPage->getLayout()->getBlock('iwd.authcim.edit.tab.cards.list');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        return $infoFormContainer->toHtml();
    }
}
