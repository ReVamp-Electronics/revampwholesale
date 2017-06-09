<?php

namespace IWD\OrderManager\Controller\Adminhtml\Invoice\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use IWD\OrderManager\Model\Invoice\Log\Logger;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Invoice\Info
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|null
     */
    protected $invoice = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $actionType
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        InvoiceRepositoryInterface $invoiceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $actionType = self::ACTION_UPDATE
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            $actionType
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @return string|string[]
     */
    protected function getResultHtml()
    {
        $this->updateInvoice();
        return $this->prepareResponse();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->loadInvoice()->getOrderId();
    }

    /**
     * @return void
     */
    public function addLogs()
    {
        Logger::getInstance()->saveLogs($this->invoice);
    }

    /**
     * @return array
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function updateInvoice()
    {
        $this->loadInvoice();

        Logger::getInstance()->addMessageForLevel('invoice_info', 'Invoice information was changed');

        $this->updateIncrementId();
        $this->updateCreatedAt();
        $this->updateStatus();

        $this->invoiceRepository->save($this->invoice);
    }

    /**
     * @return void
     */
    public function updateIncrementId()
    {
        $incrementId = $this->getIncrementId();
        Logger::getInstance()->addChange(
            'Increment Id',
            $this->invoice->getIncrementId(),
            $incrementId,
            'invoice_info'
        );
        $this->invoice->setIncrementId($incrementId);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getIncrementId()
    {
        $incrementId = $this->getInvoiceData('increment_id');
        $incrementId = trim($incrementId);

        if ($this->invoice->getIncrementId() == $incrementId) {
            return $incrementId;
        }

        if (empty($incrementId)) {
            throw new LocalizedException(__("Invoice number is empty"));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $collection = $this->invoiceRepository->getList($searchCriteria);

        if ($collection->getTotalCount() > 0) {
            throw new LocalizedException(__("Invoice number #$incrementId is already exists"));
        }

        return $incrementId;
    }

    /**
     * @return void
     */
    public function updateCreatedAt()
    {
        $createdAt = $this->getInvoiceData('created_at');
        Logger::getInstance()->addChange('Created At', $this->invoice->getCreatedAt(), $createdAt, 'invoice_info');
        $this->invoice->setCreatedAt($createdAt);
    }

    /**
     * @return void
     */
    public function updateStatus()
    {
        $status = $this->getInvoiceData('status');
        $statuses = $this->getStatusList();
        $old = $statuses[$this->invoice->getState()];
        $new = $statuses[$status];
        Logger::getInstance()->addChange('Status', $old, $new, 'invoice_info');
        $this->invoice->setState($status);
    }

    /**
     * @return string[]
     */
    public function getStatusList()
    {
        return $this->invoiceRepository->create()->getStates();
    }

    /**
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     * @throws \Exception
     */
    protected function loadInvoice()
    {
        if ($this->invoice == null) {
            $invoiceId = $this->getInvoiceId();
            $this->invoice = $this->invoiceRepository->get($invoiceId);
            if (!$this->invoice->getEntityId()) {
                throw new LocalizedException(__('Can not load invoice with id ' . $invoiceId));
            }
        }

        return $this->invoice;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getInvoiceId()
    {
        $id = $this->getRequest()->getParam('invoice_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param invoice_id'));
        }

        return $id;
    }

    /**
     * @param bool|string $id
     * @return array|string
     * @throws LocalizedException
     */
    protected function getInvoiceData($id = false)
    {
        $data = $this->getRequest()->getParam('invoice_info', []);

        if (empty($id)) {
            return $data;
        } elseif (isset($data[$id]) && !empty($data[$id])) {
            return $data[$id];
        }

        throw new LocalizedException(__('Empty param invoice_id[' . $id . ']'));
    }
}
