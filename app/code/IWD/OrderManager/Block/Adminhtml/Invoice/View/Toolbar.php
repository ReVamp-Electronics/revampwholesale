<?php

namespace IWD\OrderManager\Block\Adminhtml\Invoice\View;

use IWD\OrderManager\Model\Invoice\Invoice;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Toolbar
 * @package IWD\OrderManager\Block\Adminhtml\Invoice\View
 */
class Toolbar extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * Toolbar constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Invoice $invoice
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Invoice $invoice,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->invoice = $invoice;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->isAllowDeleteInvoice()) {
            $this->addDeleteButton();
        }
    }

    /**
     * @return void
     */
    protected function addDeleteButton()
    {
        $message = __('Are you sure you want to DELETE an invoice?');
        $url = $this->getDeleteUrl();
        $this->addButton(
            'iwd_invoice_delete',
            [
                'label'   => 'Delete',
                'class'   => 'delete',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')",
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isAllowDeleteInvoice()
    {
        $invoiceId = $this->getInvoiceId();
        $invoice = $this->invoice->load($invoiceId);

        return $invoice->isAllowDeleteInvoice();
    }

    /**
     * @return string
     */
    protected function getDeleteUrl()
    {
        return $this->getUrl('iwdordermanager/invoice/delete', ['invoice_id' => $this->getInvoiceId()]);
    }

    /**
     * @return integer
     */
    protected function getInvoiceId()
    {
        return $this->coreRegistry->registry('current_invoice')->getId();
    }
}
