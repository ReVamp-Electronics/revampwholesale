<?php

namespace IWD\OrderManager\Model\Invoice\Log;

/**
 * Class Logger
 * @package IWD\OrderManager\Model\Invoice\Log
 */
class Logger extends \IWD\OrderManager\Model\Log\Logger
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    public function saveLogs($invoice)
    {
        $this->saveLogsAsInvoiceComment($invoice);
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     */
    private function saveLogsAsInvoiceComment($invoice)
    {
        $comment = $this->getFormattedLogString();

        if (empty($comment)) {
            return;
        }
        try {
            $notify = false;
            $visible = false;
            $comment .= $this->addAdminInfo();

            $invoice->unsetData(\Magento\Sales\Api\Data\InvoiceInterface::COMMENTS);
            $invoice->addComment($comment, $notify, $visible);
            $invoice->save();
        } catch (\Exception $e) {
            $this->psrLogger->expects($e);
        }
    }
}
