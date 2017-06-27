<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Items;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;

/**
 * Class AddResult
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Items
 */
class AddResult extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_items_add';

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultRawFactory->create();
        $json = $this->prepareResponse();
        $result->setContents($json);

        return $result;
    }

    /**
     * @return string
     */
    private function prepareResponse()
    {
        $json = '{"error":"Can not get response","status":"false"}';

        if ($this->_session->hasIwdOmAddedItemsResult()
            && $this->_session->getIwdOmAddedItemsResult() instanceof DataObject
        ) {
            $json = $this->_session->getIwdOmAddedItemsResult()->toJson();
        }

        $this->_session->unsIwdOmAddedItemsResult();

        return "<script type=\"text/javascript\">//<![CDATA[ \r\n var iFrameResponse = ". $json . ";\r\n //]]></script>";
    }
}
