<?php

namespace IWD\OrderManager\Model\Shipment\Log;

/**
 * Class Logger
 * @package IWD\OrderManager\Model\Shipment\Log
 */
class Logger extends \IWD\OrderManager\Model\Log\Logger
{
    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     */
    public function saveLogs($shipment)
    {
        $this->saveLogsAsInvoiceComment($shipment);
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     */
    private function saveLogsAsInvoiceComment($shipment)
    {
        $comment = $this->getFormattedLogString();

        if (empty($comment)) {
            return;
        }
        try {
            $notify = false;
            $visible = false;
            $comment .= $this->addAdminInfo();

            $shipment->unsetData(\Magento\Sales\Api\Data\ShipmentInterface::COMMENTS);
            $shipment->addComment($comment, $notify, $visible);
            $shipment->save();
        } catch (\Exception $e) {
            $this->psrLogger->expects($e);
        }
    }
}
