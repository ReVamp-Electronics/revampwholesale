<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use IWD\OrderManager\Model\Quote\Item;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\DataObject;

class RemoveQuoteItem extends Action
{
    /**
     * @var Item
     */
    protected $_quoteItem;

    /**
     * @var DataObject
     */
    private $updateResult;

    /**
     * RemoveQuoteItem constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Item $quoteItem
     * @param DataObject $updateResult
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Item $quoteItem,
        DataObject $updateResult
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->_quoteItem = $quoteItem;
        $this->updateResult = $updateResult;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $response = [
                'result' => $this->prepareResultHtml(),
                'status' => true
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => $e->getMessage(),
                'status' => false
            ];
        }

        $updateResult = $this->updateResult->addData($response);
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($updateResult);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function prepareResultHtml()
    {
        $quoteItem = $this->_quoteItem->load($this->getQuoteId());

        $quoteItems = $quoteItem->getChildren();
        foreach ($quoteItems as $item) {
            $item->delete();
        }

        $quoteItem->delete();

        return 'true';
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getQuoteId()
    {
        $quoteId = $this->getRequest()->getParam('id', 0);
        if (!$quoteId) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Quote item id is not received.')
            );
        }

        $prefixIdLength = strlen(Item::PREFIX_ID);
        if (substr($quoteId, 0, $prefixIdLength) == Item::PREFIX_ID) {
            $quoteId = substr($quoteId, $prefixIdLength, strlen($quoteId));
        }

        return $quoteId;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed('IWD_OrderManager::iwdordermanager_items_add');
    }
}
