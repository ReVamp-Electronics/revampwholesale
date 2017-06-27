<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class AuthorizeHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
class AuthorizeHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * @return bool
     */
    public function isTransactionClosed()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isParentTransactionClosed()
    {
        return false;
    }
}
