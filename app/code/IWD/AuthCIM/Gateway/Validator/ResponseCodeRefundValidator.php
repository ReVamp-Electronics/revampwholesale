<?php

namespace IWD\AuthCIM\Gateway\Validator;

use IWD\AuthCIM\Gateway\Response\ParseResponse;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class ResponseCodeRefundValidator
 * @package IWD\AuthCIM\Gateway\Validator
 */
class ResponseCodeRefundValidator extends AbstractValidator
{
    /**
     * @var ParseResponse
     */
    private $parseResponse;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ParseResponse $parseResponse
    ) {
        parent::__construct($resultFactory);
        $this->parseResponse = $parseResponse;
    }

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->parseResponse->isSuccessful($response) || $this->isUnsettledTransactionYet($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * @param $response
     * @return bool
     */
    private function isUnsettledTransactionYet($response)
    {
        $directResponse = $this->parseResponse->parseResponse($response);
        return $directResponse->getResponseReasonCode() == 54;
    }
}
