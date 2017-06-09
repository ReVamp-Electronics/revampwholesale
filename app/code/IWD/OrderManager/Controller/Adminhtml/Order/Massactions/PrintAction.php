<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Massactions;

use IWD\OrderManager\Model\Pdf\OrderFactory;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PrintAction
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Massactions
 */
class PrintAction extends AbstractMassAction
{
    /**
     * @var OrderFactory
     */
    protected $orderPdfFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param OrderFactory $orderPdfFactory
     * @param DateTime $date
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        OrderFactory $orderPdfFactory,
        DateTime $date
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->date = $date;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $pdf = $this->orderPdfFactory->create()->getPdf($collection);
        $fileName = 'order_' . $this->date->date('Y-m-d_H-i-s') . '.pdf';

        return $this->fileFactory->create(
            $fileName,
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_OrderManager::iwdordermanager_print')
            && $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }
}
