<?php

namespace IWD\OrderManager\Controller\Adminhtml\Invoice;

use IWD\OrderManager\Model\Invoice\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package IWD\OrderManager\Controller\Adminhtml\Invoice
 */
class Delete extends Action
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
     * @var null|int
     */
    private $orderId = null;

    /**
     * @param Context $context
     * @param Invoice $invoice
     */
    public function __construct(
        Context $context,
        Invoice $invoice
    ) {
        parent::__construct($context);
        $this->invoice = $invoice;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->deleteInvoice();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('There was an error when trying to delete the invoice. Please try again.') . $e->getMessage()
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->orderId !== null) {
            $resultRedirect->setPath('sales/order/view', ['order_id' => $this->orderId]);
        } else {
            $resultRedirect->setPath('sales/invoice/index');
        }

        return $resultRedirect;
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function deleteInvoice()
    {
        $invoiceId = $this->getInvoiceId();
        $invoice = $this->invoice->load($invoiceId);
        $this->orderId = $invoice->getOrderId();
        $incrementId = $invoice->getIncrementId();

        if ($invoice->isAllowDeleteInvoice()) {
            $invoice->deleteInvoice();
            $this->messageManager->addSuccessMessage(
                __('You have successfully deleted invoice #%1.', $incrementId)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Deletion of invoices is not permitted. You may enable this option in the Order Manager settings.')
            );
        }
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getInvoiceId()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id', null);
        if (empty($invoiceId)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $invoiceId;
    }
}
