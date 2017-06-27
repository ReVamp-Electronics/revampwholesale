<?php

namespace IWD\OrderManager\Controller\Adminhtml\Invoice\Massactions;

use IWD\OrderManager\Model\Invoice\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;

class Delete extends AbstractMassAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_delete_invoice';

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Invoice $invoice
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Invoice $invoice
    ) {
        parent::__construct($context, $filter);
        $this->invoice = $invoice;
        $this->collectionFactory = $collectionFactory;
    }
    /**
     * {@inheritdoc}
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeletedInvoice = 0;
        foreach ($collection->getItems() as $item) {
            $invoice = clone $this->invoice->load($item->getId());
            if ($invoice->isAllowDeleteInvoice()) {
                $invoice->deleteInvoice();
                $countDeletedInvoice++;
            }
        }

        $countNonDeletedInvoices = count($collection->getItems()) - $countDeletedInvoice;

        if ($countNonDeletedInvoices && $countDeletedInvoice) {
            $this->messageManager->addErrorMessage(
                __('Invoice %1 could not be deleted as deletion of invoices is not permitted. You may enable this option in the Order Manager settings.', $countNonDeletedInvoices)
            );
        } elseif ($countNonDeletedInvoices) {
            $this->messageManager->addErrorMessage(
                __('Invoice could not be deleted as deletion of invoices is not permitted. You may enable this option in the Order Manager settings.')
            );
        }

        if ($countDeletedInvoice) {
            $this->messageManager->addSuccessMessage(__('Invoice %1 has been deleted.', $countDeletedInvoice));
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
        return 'sales/invoice/index';
    }
}
