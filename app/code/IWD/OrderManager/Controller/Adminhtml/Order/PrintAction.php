<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order;

use IWD\OrderManager\Model\Pdf\OrderFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintAction
 * @package IWD\OrderManager\Controller\Adminhtml\Order
 */
class PrintAction extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_print';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var OrderFactory
     */
    protected $orderPdfFactory;

    /**
     * PrintAction constructor.
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFactory $orderPdfFactory
     * @param DateTime $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderPdfFactory,
        FileFactory $fileFactory,
        DateTime $date
    ) {
        parent::__construct($context);

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->orderRepository = $orderRepository;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->fileFactory = $fileFactory;
        $this->date = $date;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            return $this->generateOrderPdf();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('sales/*/view');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function generateOrderPdf()
    {
        $order = $this->getOrder();
        $pdf = $this->orderPdfFactory->create()->getPdf([$order]);
        $fileName = 'order_' . $this->date->date('Y-m-d_H-i-s') . '.pdf';

        return $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Exception
     */
    protected function getOrder()
    {
        $orderId = $this->getOrderId();
        $order = $this->orderRepository->get($orderId);

        if (!$order) {
            throw new LocalizedException(__('Can not load order'));
        }

        return $order;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if (empty($orderId)) {
            throw new LocalizedException(__('Empty param id'));
        }

        return $orderId;
    }
}
