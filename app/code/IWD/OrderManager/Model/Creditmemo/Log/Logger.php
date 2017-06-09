<?php

namespace IWD\OrderManager\Model\Creditmemo\Log;

/**
 * Class Logger
 * @package IWD\OrderManager\Model\Creditmemo\Log
 */
class Logger extends \IWD\OrderManager\Model\Log\Logger
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     */
    public function saveLogs($creditmemo)
    {
        $this->saveLogsAsInvoiceComment($creditmemo);
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     */
    private function saveLogsAsInvoiceComment($creditmemo)
    {
        $comment = $this->getFormattedLogString();

        if (empty($comment)) {
            return;
        }
        try {
            $notify = false;
            $visible = false;
            $comment .= $this->addAdminInfo();

            $creditmemo->unsetData(\Magento\Sales\Api\Data\CreditmemoInterface::COMMENTS);
            $creditmemo->addComment($comment, $notify, $visible);
            $creditmemo->save();
        } catch (\Exception $e) {
            $this->psrLogger->expects($e);
        }
    }
}
