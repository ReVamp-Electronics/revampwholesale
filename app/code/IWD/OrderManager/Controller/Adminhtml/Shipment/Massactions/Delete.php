<?php

namespace IWD\OrderManager\Controller\Adminhtml\Shipment\Massactions;

use IWD\OrderManager\Model\Shipment\Shipment;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Shipment\Massactions
 */
class Delete extends AbstractMassAction
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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Shipment $shipment
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Shipment $shipment
    ) {
        parent::__construct($context, $filter);
        $this->shipment = $shipment;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeletedShipments = 0;
        foreach ($collection->getItems() as $item) {
            $shipment = clone $this->shipment->load($item->getId());
            if ($shipment->isAllowDeleteShipment()) {
                $shipment->deleteShipment();
                $countDeletedShipments++;
            }
        }
        $countNonDeletedShipments = count($collection->getItems()) - $countDeletedShipments;

        if ($countNonDeletedShipments && $countDeletedShipments) {
            $this->messageManager->addErrorMessage(
                __('Shipment %1 could not be deleted as deletion of shipments is not permitted. You may enable this option in the Order Manager settings.', $countNonDeletedShipments)
            );
        } elseif ($countNonDeletedShipments) {
            $this->messageManager->addErrorMessage(
                __('Shipment could not be deleted as deletion of shipments is not permitted. You may enable this option in the Order Manager settings.')
            );
        }

        if ($countDeletedShipments) {
            $this->messageManager->addSuccessMessage(
                __('You have successfully deleted %1 shipment.', $countDeletedShipments)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * {@inheritdoc}
     */
    protected function getComponentRefererUrl()
    {
        return 'sales/shipment/index';
    }
}
