<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class FetchRequest
 * @package IWD\AuthCIM\Gateway\Request\Payment
 */
class FetchRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        return [
            'root' => 'getTransactionDetailsRequest',
            'transId' => $this->getTransactionId()
        ];
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    private function getTransactionId()
    {
        $buildSubject = $this->getBuildSubject();

        if (!isset($buildSubject['transactionId']) || empty($buildSubject['transactionId'])) {
            throw new LocalizedException(__('Transaction id is empty'));
        }

        $transactionId = $buildSubject['transactionId'];
        $transactionId = explode('-', $transactionId);

        return isset($transactionId[0]) ? $transactionId[0] : $buildSubject['transactionId'];
    }
}
