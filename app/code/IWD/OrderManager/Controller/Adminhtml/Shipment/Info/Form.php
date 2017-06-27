<?php

namespace IWD\OrderManager\Controller\Adminhtml\Shipment\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

class Form extends AbstractAction
{
    /**
     * @return string
     * @throws \Exception
     */
    protected function getResultHtml()
    {
        $resultPage = $this->resultPageFactory->create();

        /** @var \IWD\OrderManager\Block\Adminhtml\Shipment\Info\Form $infoFormContainer */
        $infoFormContainer = $resultPage->getLayout()->getBlock('iwdordermamager_shipment_info_form');
        if (empty($infoFormContainer)) {
            throw new LocalizedException(__('Can not load block'));
        }

        $shipmentId = $this->getShipmentId();
        $infoFormContainer->setShipmentId($shipmentId);

        return $infoFormContainer->toHtml();
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getShipmentId()
    {
        $id = $this->getRequest()->getParam('shipment_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param shipment id'));
        }
        return $id;
    }
}
