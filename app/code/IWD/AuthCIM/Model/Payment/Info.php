<?php

namespace IWD\AuthCIM\Model\Payment;

use IWD\AuthCIM\Model\Card;
use Magento\Framework\Exception\LocalizedException;

class Info
{
    /**
     * @var array
     */
    private $ccKeys = [
        "cc_exp_year",
        "cc_exp_month",
        "cc_number",
        "cc_cid"
    ];

    /**
     * @var array
     */
    private $additionalData = [];

    /**
     * @param $payment
     * @param $additionalData
     * @return mixed
     * @throws LocalizedException
     */
    public function preparePaymentInfo(&$payment, $additionalData)
    {
        $this->additionalData = $additionalData;
        $ccId = $this->getCcId();
        $payment->setAdditionalInformation(Card::SAVED_CC_ID, $ccId);

        if ($this->isExistsCreditCard()) {
            if ($this->isOpaqueData()) {
                $payment->setAdditionalInformation(Card::OPAQUE_DESCRIPTION, $this->getOpaqueDescriptor());
                $payment->setAdditionalInformation(Card::OPAQUE_VALUE, $this->getOpaqueValue());
                $payment->setAdditionalInformation(Card::IS_SAVE_CC, $this->getIsSaveCc());
                $payment->setAdditionalInformation(Card::CARD_TYPE, $this->getCardType());
                $payment->setAdditionalInformation(Card::CARD_LAST_4, $this->getLast4FromOpaque());
            } elseif ($this->isBankAccount()) {
                $payment->setAdditionalInformation(Card::ACCOUNT_TYPE, $this->getAccountType());
                $payment->setAdditionalInformation(Card::ROUTING_NUMBER, $this->getRoutingNumber());
                $payment->setAdditionalInformation(Card::ACCOUNT_NUMBER, $this->getAccountNumber());
                $payment->setAdditionalInformation(Card::NAME_ON_ACCOUNT, $this->getNameOnAccount());
                $payment->setAdditionalInformation(Card::ECHECK_TYPE, $this->getEcheckType());
                $payment->setAdditionalInformation(Card::BANK_NAME, $this->getBankName());
            } elseif ($this->isCreditCard()) {
                $payment->setAdditionalInformation(Card::IS_SAVE_CC, $this->getIsSaveCc());
                $payment->setAdditionalInformation(Card::CARD_TYPE, $this->getCardType());
                $payment->setAdditionalInformation(Card::CARD_LAST_4, $this->getLast4());
            } else {
                throw new LocalizedException(__('Incorrect payment data type.'));
            }
        }

        $ccData = array_intersect_key($this->additionalData, array_flip($this->ccKeys));
        foreach ($ccData as $ccKey => $ccValue) {
            $payment->setData($ccKey, $ccValue);
        }

        return $payment;
    }

    /**
     * @return bool
     */
    private function isCreditCard()
    {
        return isset($this->additionalData['cc_number']);
    }

    /**
     * @return bool
     */
    private function isOpaqueData()
    {
        return isset($this->additionalData[Card::OPAQUE_DESCRIPTION])
            && isset($this->additionalData[Card::OPAQUE_VALUE]);
    }

    /**
     * @return bool
     */
    private function isBankAccount()
    {
        return isset($this->additionalData[Card::ROUTING_NUMBER])
            && isset($this->additionalData[Card::ACCOUNT_NUMBER]);
    }

    /**
     * @return bool
     */
    private function isExistsCreditCard()
    {
        $ccId = $this->getCcId();
        return ("$ccId" == "0");
    }

    /**
     * @return string
     */
    private function getCcId()
    {
        return (isset($this->additionalData[Card::SAVED_CC_ID])) ? $this->additionalData[Card::SAVED_CC_ID] : 0;
    }

    /**
     * @return string
     */
    private function getIsSaveCc()
    {
        return isset($this->additionalData[Card::IS_SAVE_CC]) ? true : false;
    }

    /**
     * @return string
     */
    private function getLast4()
    {
        return isset($this->additionalData['cc_number'])
            ? 'xxxx-' . substr($this->additionalData['cc_number'], -4)
            : 'xxxx';
    }

    /**
     * @return string
     */
    private function getLast4FromOpaque()
    {
        return isset($this->additionalData[Card::OPAQUE_NUMBER])
            ? 'xxxx-' . substr($this->additionalData[Card::OPAQUE_NUMBER], -4)
            : 'xxxx';
    }

    /**
     * @return mixed|string
     */
    private function getCardType()
    {
        $ccType = isset($this->additionalData['cc_type']) ? $this->additionalData['cc_type'] : '';
        return \IWD\AuthCIM\Helper\Data::getCreditCardType($ccType);
    }

    /**
     * @return string
     */
    private function getOpaqueDescriptor()
    {
        return isset($this->additionalData[Card::OPAQUE_DESCRIPTION])
            ? $this->additionalData[Card::OPAQUE_DESCRIPTION]
            : '';
    }

    /**
     * @return string
     */
    private function getOpaqueValue()
    {
        return isset($this->additionalData[Card::OPAQUE_VALUE]) ? $this->additionalData[Card::OPAQUE_VALUE] : '';
    }

    /**
     * @return string
     */
    private function getAccountType()
    {
        return isset($this->additionalData[Card::ACCOUNT_TYPE]) ? $this->additionalData[Card::ACCOUNT_TYPE] : '';
    }

    /**
     * @return string
     */
    private function getRoutingNumber()
    {
        return isset($this->additionalData[Card::ROUTING_NUMBER]) ? $this->additionalData[Card::ROUTING_NUMBER] : '';
    }

    /**
     * @return string
     */
    private function getAccountNumber()
    {
        return isset($this->additionalData[Card::ACCOUNT_NUMBER]) ? $this->additionalData[Card::ACCOUNT_NUMBER] : '';
    }

    /**
     * @return string
     */
    private function getNameOnAccount()
    {
        return isset($this->additionalData[Card::NAME_ON_ACCOUNT]) ? $this->additionalData[Card::NAME_ON_ACCOUNT] : '';
    }

    /**
     * @return string
     */
    private function getEcheckType()
    {
        return isset($this->additionalData[Card::ECHECK_TYPE]) ? $this->additionalData[Card::ECHECK_TYPE] : '';
    }

    /**
     * @return string
     */
    private function getBankName()
    {
        return isset($this->additionalData[Card::BANK_NAME]) ? $this->additionalData[Card::BANK_NAME] : '';
    }
}
