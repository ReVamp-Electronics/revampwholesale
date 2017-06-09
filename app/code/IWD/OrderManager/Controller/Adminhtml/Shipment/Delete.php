<?php

namespace IWD\OrderManager\Controller\Adminhtml\Shipment;

use IWD\OrderManager\Model\Shipment\Shipment;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/***
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Shipment
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_delete_shipment';

    /**
     * @var Shipment
     */
    private $shipment;

    /**
     * @var null|int
     */
    private $orderId = null;

    /**
     * @param Context $context
     * @param Shipment $shipment
     */
    public function __construct(
        Context $context,
        Shipment $shipment
    ) {
        parent::__construct($context);
        $this->shipment = $shipment;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->deleteShipment();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error when trying to delete the shipment. Please try again. ') . $e->getMessage()
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->orderId !== null) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $this->orderId]);
        } else {
            $resultRedirect->setPath('sales/shipment/index');
        }

        return $resultRedirect;
    }

    /**
     * @return void
     */
    private function deleteShipment()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id', null);
        $shipment = $this->shipment->load($shipmentId);
        $this->orderId = $shipment->getOrderId();
        $incrementId = $shipment->getIncrementId();

        if ($shipment->isAllowDeleteShipment()) {
            $shipment->deleteShipment();
            $this->messageManager->addSuccessMessage(__('You have successfully deleted shipment #%1.', $incrementId));
        } else {
            $this->messageManager->addErrorMessage(
                __('Deletion of shipments is not permitted. You may enable this option in the Order Manager settings.')
            );
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getShipmentId()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id', null);
        if (empty($shipmentId)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $shipmentId;
    }
}
