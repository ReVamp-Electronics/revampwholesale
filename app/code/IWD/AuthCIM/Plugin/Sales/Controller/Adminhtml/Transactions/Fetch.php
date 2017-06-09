<?php

namespace IWD\AuthCIM\Plugin\Sales\Controller\Adminhtml\Transactions;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Framework\App\Action\Context;

/**
 * Class Fetch
 * @package IWD\AuthCIM\Plugin\Sales\Controller\Adminhtml\Transactions
 * @see \Magento\Sales\Controller\Adminhtml\Transactions\Fetch
 */
class Fetch
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * Fetch constructor.
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param LoggerInterface $log
     * @param TransactionRepositoryInterface $transactionRepository
     * @param Registry $coreRegistry
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        LoggerInterface $log,
        TransactionRepositoryInterface $transactionRepository,
        Registry $coreRegistry,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->urlInterface = $context->getUrl();
        $this->resultFactory = $context->getResultFactory();
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();

        $this->productMetadata = $productMetadata;
        $this->log = $log;
        $this->transactionRepository = $transactionRepository;
        $this->coreRegistry = $coreRegistry;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param \Magento\Sales\Controller\Adminhtml\Transactions\Fetch $subject
     * @param \Closure $proceed
     * @return Fetch
     */
    public function aroundExecute(\Magento\Sales\Controller\Adminhtml\Transactions\Fetch $subject, \Closure $proceed)
    {
        /**
         * - https://github.com/magento/magento2/issues/8165
         * - https://github.com/magento/magento2/issues/4976
         */
        $fixedInVersion = '2.1.4'; //hope it will be fixed in nex release
        $magentoVersion = $this->productMetadata->getVersion();

        $isFixed = version_compare($magentoVersion, $fixedInVersion, '>=');

        if ($isFixed) {
            return $proceed();
        } else {
            return $this->execute($proceed);
        }
    }

    /**
     * @param $proceed
     * @return $this
     */
    public function execute($proceed)
    {
        $txn = $this->initTransaction();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$txn) {
            return $resultRedirect->setPath('sales/*/');
        }
        try {
            $payment = $this->orderPaymentRepository->get($txn->getPaymentId());

            if (\IWD\AuthCIM\Model\Ui\ConfigProvider::CODE != $payment->getMethod()) {
                return $proceed();
            }

            $payment->setOrder($txn->getOrder())
                ->importTransactionInfo($txn);
            $this->messageManager->addSuccessMessage(__('The transaction details have been updated.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update the transaction details.'));
            $this->log->critical($e);
        }

        return $resultRedirect->setPath('sales/transactions/view', ['_current' => true]);
    }

    /**
     * @return bool|\Magento\Sales\Api\Data\TransactionInterface
     */
    private function initTransaction()
    {
        $txnId = $this->request->getParam('txn_id');

        $txn = $this->transactionRepository->get($txnId);

        if (!$txn->getId()) {
            $this->messageManager->addErrorMessage(__('Please correct the transaction ID and try again.'));
            return false;
        }
        $orderId = $this->request->getParam('order_id');
        if ($orderId) {
            $txn->setOrderUrl($this->urlInterface->getUrl('sales/order/view', ['order_id' => $orderId]));
        }

        $this->coreRegistry->register('current_transaction', $txn);
        return $txn;
    }
}
