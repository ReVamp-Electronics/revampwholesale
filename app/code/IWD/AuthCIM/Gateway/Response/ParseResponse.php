<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

/**
 * Class ParseResponse
 * @package IWD\AuthCIM\Gateway\Response
 */
class ParseResponse
{
    /**
     * @var DataObject
     */
    private $directResponse;

    /**
     * @param DataObject $directResponse
     */
    public function __construct(DataObject $directResponse)
    {
        $this->directResponse = $directResponse;
    }

    /**
     * @param $response
     * @return bool
     */
    public function isError($response)
    {
        return !$this->isSuccessful($response);
    }

    /**
     * @param $response
     * @return bool
     */
    public function isSuccessful($response)
    {
        if (isset($response['messages']['resultCode']) && $response['messages']['resultCode'] == 'Ok') {
            return !(isset($response['transactionResponse']['errors']));
        }

        return false;
    }

    /**
     * @param $response
     * @return null|string
     */
    public function getErrorCode($response)
    {
        if (isset($response['messages']['message']['code'])) {
            return $response['messages']['message']['code'];
        }
        return null;
    }

    /**
     * @param $response
     * @return string
     */
    public function getErrorMessage($response)
    {
        $message = '';
        if (isset($response['messages']['message'])) {
            $error = $response['messages']['message'];
            if (isset($error['code'])) {
                $message .= '(' . $error['code'] . ') ';
            }
            if (isset($error['text'])) {
                $message .= $error['text'];
            }
        } else {
            $message = 'Undefined Error';
        }

        return 'Gateway Error: ' . $message;
    }

    /**
     * @param $response
     * @return DataObject
     * @throws LocalizedException
     */
    public function parseResponse($response)
    {
        if (isset($response['transactionResponse'])) {
            $data = $this->parseTransactionResponse($response);
        } else {
            $data = $this->parseDirectResponse($response);
        }

        return $this->directResponse->setData($data);
    }

    /**
     * @param $response
     * @return array
     */
    private function parseTransactionResponse($response)
    {
        $response = $response['transactionResponse'];

        return [
            'response_code' => (int)$response['responseCode'],
            'auth_code' => $response['authCode'],
            'avs_result_code' => $response['avsResultCode'],
            'cavv_response_code' => $response['cavvResultCode'],
            'transaction_id' => $response['transId'],
            'cvv_result_code' => $response['cvvResultCode'],
            'ref_trans_id' => $response['refTransID'],
            'trans_hash' => $response['transHash'],
            'test_request' => $response['testRequest'],
            'account_number' => $response['accountNumber'],
            'account_type' => $response['accountType'],
            'trans_hash_sha2' => $response['transHashSha2']
        ];
    }

    /**
     * @param $response
     * @return array
     * @throws LocalizedException
     */
    private function parseDirectResponse($response)
    {
        $directResponse = isset($response['directResponse'])
            ? $response['directResponse']
            : (isset($response['validationDirectResponse']) ? $response['validationDirectResponse'] : '');

        if (strlen($directResponse) > 1) {
            $directResponse = str_replace('"', '', $directResponse);
            $directResponse = explode(substr($directResponse, 1, 1), $directResponse);
        }

        if (empty($directResponse) || count($directResponse) == 0) {
            throw new LocalizedException(__('Authorize.Net CIM Gateway: Transaction failed - no direct response.'));
        }

        return [
            'response_code' => (int)$directResponse[0],
            'response_subcode' => (int)$directResponse[1],
            'response_reason_code' => (int)$directResponse[2],
            'response_reason_text' => $directResponse[3],
            'approval_code' => $directResponse[4],
            'auth_code' => $directResponse[4],
            'avs_result_code' => $directResponse[5],
            'transaction_id' => $directResponse[6],
            'invoice_number' => $directResponse[7],
            'description' => $directResponse[8],
            'amount' => $directResponse[9],
            'method' => $directResponse[10],
            'transaction_type' => $directResponse[11],
            'customer_id' => $directResponse[12],
            'md5_hash' => $directResponse[37],
            'card_code_response_code' => $directResponse[38],
            'cavv_response_code' => $directResponse[39],
            'acc_number' => $directResponse[50],
            'card_type' => $directResponse[51],
            'split_tender_id' => $directResponse[52],
            'requested_amount' => $directResponse[53],
            'balance_on_card' => $directResponse[54]
        ];
    }
}
