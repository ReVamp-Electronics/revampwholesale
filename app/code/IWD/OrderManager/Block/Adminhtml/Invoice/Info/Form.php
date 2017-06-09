<?php

namespace IWD\OrderManager\Block\Adminhtml\Invoice\Info;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Invoice\Info
 */
class Form extends AbstractForm
{
    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface
     */
    private $invoice;

    /**
     * @var int
     */
    private $invoiceId;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    public function getInvoice()
    {
        if ($this->invoice == null) {
            $id = $this->getInvoiceId();
            $this->invoice = $this->invoiceRepository->get($id);
        }

        return $this->invoice;
    }

    /**
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @return string[]
     */
    public function getStatusList()
    {
        return $this->invoiceRepository->create()->getStates();
    }
}
