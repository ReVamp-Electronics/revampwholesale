<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class Search
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Items
 */
class Search extends Create
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_items_add';

    /**
     * @var DataObject
     */
    private $updateResult;

    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        DataObject $updateResult
    ) {
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);
        $this->updateResult = $updateResult;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $resultPage = $this->resultPageFactory->create();
            $html = '';

            $optionsBlock = $resultPage->getLayout()->getBlock('search');
            if (!empty($optionsBlock)) {
                $html .= $optionsBlock->toHtml();
            }

            $createBlock = $resultPage->getLayout()->getBlock('create');
            if (!empty($createBlock)) {
                $html .= $createBlock->toHtml();
            }

            $this->updateResult->setSearchGrid($html);
            $this->updateResult->setOk(true);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->updateResult->setError(true);
            $this->updateResult->setMessage($errorMessage);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($this->updateResult);
    }
}
