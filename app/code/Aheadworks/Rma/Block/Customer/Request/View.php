<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request;

use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class View
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/request/view.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @var null|\Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    protected $customFieldCollection = null;

    /**
     * @var \Aheadworks\Rma\Helper\CustomField
     */
    protected $customFieldHelper;

    /**
     * @var \Aheadworks\Rma\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Aheadworks\Rma\Helper\Status
     */
    protected $statusHelper;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var null|\Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $threadMessageCollection = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var array
     */
    protected $products = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     * @param \Aheadworks\Rma\Helper\CustomField $customFieldHelper
     * @param \Aheadworks\Rma\Helper\File $fileHelper
     * @param \Aheadworks\Rma\Helper\Status $statusHelper
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Aheadworks\Rma\Helper\CustomField $customFieldHelper,
        \Aheadworks\Rma\Helper\File $fileHelper,
        \Aheadworks\Rma\Helper\Status $statusHelper,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->priceCurrency = $priceCurrency;
        $this->productFactory = $productFactory;
        $this->customerSession = $customerSession;
        $this->customFieldFactory = $customFieldFactory;
        $this->customFieldHelper = $customFieldHelper;
        $this->fileHelper = $fileHelper;
        $this->statusHelper = $statusHelper;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return \Aheadworks\Rma\Model\Request
     */
    public function getRequestModel()
    {
        return $this->coreRegistry->registry('aw_rma_request');
    }

    /**
     * @return int|string
     */
    public function getRequestIdentityValue()
    {
        return $this->getRequestModel()->getId();
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    public function getCustomFieldCollection()
    {
        if ($this->customFieldCollection === null) {
            $storeId = $this->getRequestModel()->getStoreId();
            $this->customFieldCollection = $this->customFieldFactory->create()->getCollection()
                ->addRefersToFilter(Refers::REQUEST_VALUE)
                ->joinAttributesValues(['frontend_label'], $storeId)
                ->setStoreId($storeId)
            ;
        }
        return $this->customFieldCollection;
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection|null
     */
    public function getThreadMessageCollection()
    {
        if ($this->threadMessageCollection === null) {
            $this->threadMessageCollection = $this->getRequestModel()->getThread();
        }
        return $this->threadMessageCollection;
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $customField
     * @param \Aheadworks\Rma\Model\Request $requestModel
     * @return string
     */
    public function getRequestCustomFieldsInputHtml($customField, $requestModel)
    {
        /** @var \Aheadworks\Rma\Block\CustomField\Input\Renderer\RendererAbstract $renderer */
        $renderer = $this->getLayout()->createBlock(
            $this->customFieldHelper->getElementRendererClass($customField->getType())
        );
        return $renderer
            ->setCustomField($customField)
            ->setValue($requestModel->getCustomFields($customField->getId()))
            ->setStatusId($requestModel->getStatusId())
            ->render()
            ;
    }

    /**
     * @param string $customFieldName
     * @param \Aheadworks\Rma\Model\RequestItem $requestItem
     * @return string
     */
    public function getRequestItemCustomFieldHtml($customFieldName, $requestItem)
    {
        /** @var \Aheadworks\Rma\Model\CustomField $customField */
        $customField = $this->customFieldFactory->create()->loadByName($customFieldName);
        $value = $requestItem->getCustomFieldValue($customField->getId());
        if (in_array($customField->getType(), [Type::SELECT_VALUE, Type::MULTI_SELECT_VALUE])) {
            return $customField
                ->setStoreId($this->getRequestModel()->getStoreId())
                ->getOptionLabelByValue($value);
        }
        return $value;
    }

    /**
     * @param float $amount
     * @return string
     */
    public function convertAndFormatPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * @return bool
     */
    public function canReply()
    {
        return $this->statusHelper->isAvailableForReply(
            $this->getRequestModel()->getStatusId()
        );
    }

    /**
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->_scopeConfig->getValue(
            'aw_rma/contacts/department_name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * @param $size
     * @return string
     */
    public function formatFileSize($size)
    {
        return $this->fileHelper->getTextFileSize($size);
    }

    /**
     * @param int $productId
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->products[$productId])) {
            $this->products[$productId] = $this->productFactory->create()->load($productId);
        }
        return $this->products[$productId];
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        return (bool)$this->getProduct($productId)->getId();
    }

    /**
     * @param $requestItem
     * @return string
     */
    public function getProductViewUrl($requestItem)
    {
        $product = $this->getProduct($requestItem->getProductId());
        $parentProductId = $requestItem->getParentProductId();
        if ($parentProductId) {
            $parentProduct = $this->getProduct($parentProductId);
            if (in_array($parentProduct->getTypeId(), $this->orderHelper->getNotReturnedOrderItemProductTypes())) {
                return $parentProduct->getProductUrl();
            }
        }
        return $product->getProductUrl();
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @return string
     */
    public function getSubmitReplyUrl()
    {
        return $this->getUrl('*/*/reply');
    }

    /**
     * @return string
     */
    public function getSubmitCustomFieldUrl()
    {
        return $this->getUrl('*/*/saveCustomField');
    }

    /**
     * @param int $attachmentId
     * @return string
     */
    public function getDownloadUrl($attachmentId)
    {
        $params = ['id' => $attachmentId];
        if ($this->isNeedToAddKeyParam()) {
            $rmaRequest = $this->getRequestModel();
            $customerEmail = $rmaRequest->getCustomerEmail();
            $params['key'] = md5($customerEmail);
        }
        return $this->getUrl('*/*/download', $params);
    }

    /**
     * @return bool
     */
    private function isNeedToAddKeyParam()
    {
        return !($this->customerSession->isLoggedIn());
    }
}
