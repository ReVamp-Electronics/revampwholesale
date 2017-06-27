<?php

namespace IWD\AuthCIM\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class VoidHandler
 * @package IWD\AuthCIM\Gateway\Response
 */
class VoidHandler extends AbstractHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isTransactionClosed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isParentTransactionClosed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTransactionData()
    {
        return;
    }
}
