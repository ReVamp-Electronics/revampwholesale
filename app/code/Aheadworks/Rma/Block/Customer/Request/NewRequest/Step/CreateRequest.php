<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step;

use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Aheadworks\Rma\Model\Source\CustomField\Type as CustomFieldType;

/**
 * Class CreateRequest
 * @package Aheadworks\Rma\Block\Customer\Request\NewRequest\Step
 */
class CreateRequest extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEXT_PAGE_BLOCK = 'aw_rma/blocks_and_policy/reasons_and_details_block';

    const XML_PATH_POLICY_PAGE_BLOCK = 'aw_rma/blocks_and_policy/policy_block';

    /**
     * @var string
     */
    protected $_template = 'customer/request/newrequest/step/createrequest.phtml';

    /**
     * @var bool
     */
    protected $guestMode = false;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $productImageBuilder;

    /**
     * @var \Aheadworks\Rma\Helper\CmsBlock
     */
    protected $cmsBlockHelper;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Aheadworks\Rma\Helper\CustomField
     */
    protected $customFieldHelper;

    /**
     * @var \Aheadworks\Rma\Model\CustomField|null
     */
    protected $resolution = null;

    /**
     * @var \Aheadworks\Rma\Model\CustomField|null
     */
    protected $packageCondition = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection|null
     */
    protected $orderItemCollection = null;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection|null
     */
    protected $itemCustomFieldCollection = null;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection|null
     */
    protected $requestCustomFieldCollection = null;

    /**
     * @var array
     */
    protected $itemProductUrls = [];

    /**
     * @var array|null
     */
    protected $massActionOptionArray = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $productImageBuilder
     * @param \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param \Aheadworks\Rma\Helper\CustomField $customFieldHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $productImageBuilder,
        \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        \Aheadworks\Rma\Helper\CustomField $customFieldHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->coreRegistry = $coreRegistry;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productImageBuilder = $productImageBuilder;
        $this->cmsBlockHelper = $cmsBlockHelper;
        $this->customFieldFactory = $customFieldFactory;
        $this->orderHelper = $orderHelper;
        $this->customFieldHelper = $customFieldHelper;
    }

    /**
     * @return bool
     */
    public function isGuestMode()
    {
        return $this->guestMode;
    }

    /**
     * @return string
     */
    public function getTextCmsBlockHtml()
    {
        return $this->cmsBlockHelper->getBlockHtml(self::XML_PATH_TEXT_PAGE_BLOCK);
    }

    /**
     * @return string
     */
    public function getPolicyCmsBlockHtml()
    {
        return $this->cmsBlockHelper->getBlockHtml(self::XML_PATH_POLICY_PAGE_BLOCK);
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        return $this->coreRegistry->registry('aw_rma_request_data');
    }

    /**
     * @return array
     */
    public function getFormData()
    {
        $formData = $this->coreRegistry->registry('aw_rma_form_data');
        return $formData ? : [];
    }

    /**
     * Retrieves requested order items. Return null, when all items are selected
     *
     * @return array|null
     */
    public function getRequestItems()
    {
        $requestData = $this->getRequestData();
        if ($requestData && isset($requestData['item']) && is_array($requestData['item'])) {
            return $requestData['item'];
        }
        return null;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        $requestData = $this->getRequestData();
        return $requestData['order_id'];
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    public function getRequestItemCount(\Magento\Sales\Model\Order\Item $item)
    {
        $requestItems = $this->getRequestItems();
        return $requestItems ? $requestItems[$item->getId()]['qty'] : $this->orderHelper->getItemMaxCount($item);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getItemProductUrl(\Magento\Sales\Model\Order\Item $item)
    {
        if (!array_key_exists($item->getId(), $this->itemProductUrls)) {
            $product = $item->getProduct();
            $parentItemId = $item->getParentItemId();
            if ($parentItemId) {
                $parentProduct = $this->orderItemFactory->create()
                    ->load($parentItemId)
                    ->getProduct()
                ;
                if (in_array($parentProduct->getTypeId(), $this->orderHelper->getNotReturnedOrderItemProductTypes())) {
                    $this->itemProductUrls[$item->getId()] = $parentProduct->getProductUrl();
                }
            } else {
                $this->itemProductUrls[$item->getId()] = $product->getProductUrl();
            }
        }
        return $this->itemProductUrls[$item->getId()];
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getItemProductImage(\Magento\Sales\Model\Order\Item $item)
    {
        return $this->productImageBuilder->setProduct($item->getProduct())
            ->setImageId('product_small_image')
            ->create();
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getItemProductPriceHtml(\Magento\Sales\Model\Order\Item $item)
    {
        $price = '';
        if (!$item->getProduct()) {
            $price = $this->convertAndFormatPrice($item->getPrice());
        } else {
            /** @var \Magento\Framework\Pricing\Render $priceRender */
            $priceRenderer = $this->getLayout()->getBlock('product.price.render.default');
            if ($priceRenderer) {
                $price = $priceRenderer->render(
                    \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                    $item->getProduct(),
                    ['zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST])
                ;
            }
        }
        return $price;
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    public function getItemCustomFieldCollection()
    {
        if ($this->itemCustomFieldCollection === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->itemCustomFieldCollection = $this->customFieldFactory->create()->getCollection()
                ->addRefersToFilter(Refers::ITEM_VALUE)
                ->joinAttributesValues(['frontend_label'], $storeId)
                ->setStoreId($storeId)
            ;
        }
        return $this->itemCustomFieldCollection;
    }

    /**
     * @return \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection
     */
    public function getRequestCustomFieldCollection()
    {
        if ($this->requestCustomFieldCollection === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            $this->requestCustomFieldCollection = $this->customFieldFactory->create()->getCollection()
                ->addRefersToFilter(Refers::REQUEST_VALUE)
                ->joinAttributesValues(['frontend_label'], $storeId)
                ->setStoreId($storeId)
            ;
        }
        return $this->requestCustomFieldCollection;
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $customField
     * @param string|null $htmlId
     * @return string
     */
    public function getRequestCustomFieldsInputHtml(\Aheadworks\Rma\Model\CustomField $customField, $htmlId = null)
    {
        /** @var \Aheadworks\Rma\Block\CustomField\Input\Renderer\RendererAbstract $renderer */
        $renderer = $this->getLayout()->createBlock(
            $this->customFieldHelper->getElementRendererClass($customField->getType())
        );
        $renderer
            ->setCustomField($customField)
            ->setStatusId(EditAt::NEW_REQUEST_PAGE)
            ->setWithCaptions(true)
        ;
        if ($htmlId) {
            $renderer->setHtmlId($htmlId);
        }
        return $renderer->render();
    }

    /**
     * @param \Aheadworks\Rma\Model\CustomField $customField
     * @param string|null $htmlId
     * @param bool $wrapped
     * @param string|null $name
     * @param bool|null $ignoreValidate
     * @return string
     */
    public function getItemCustomFieldsInputHtml(
        \Aheadworks\Rma\Model\CustomField $customField,
        $htmlId = null,
        $wrapped = false,
        $name = null,
        $ignoreValidate = null
    ) {
        /** @var \Aheadworks\Rma\Block\CustomField\Input\Renderer\RendererAbstract $renderer */
        $renderer = $this->getLayout()->createBlock(
            $this->customFieldHelper->getElementRendererClass($customField->getType())
        );
        $renderer
            ->setCustomField($customField)
            ->setStatusId(EditAt::NEW_REQUEST_PAGE)
            ->setWithCaptions(true)
            ->setIsWrapped($wrapped)
        ;
        if ($htmlId) {
            $renderer->setHtmlId($htmlId);
        }
        if ($name) {
            if ($customField->getType() == CustomFieldType::MULTI_SELECT_VALUE) {
                $name .= '[]';
            }
            $renderer->setName($name);
        }
        if ($ignoreValidate) {
            $renderer->setIsIgnoreValidate($ignoreValidate);
        }
        return $renderer->render();
    }

    /**
     * @return array
     */
    public function getMassActionOptionArray()
    {
        if ($this->massActionOptionArray === null) {
            foreach ($this->getItemCustomFieldCollection() as $customField) {
                $this->massActionOptionArray[] = [
                    'value' => $customField->getId(),
                    'label' => __('Change %1', $customField->getFrontendLabel())
                ];
            }
            $this->massActionOptionArray[] = [
                'value' => 'remove',
                'label' => __('Remove')
            ];
        }
        return $this->massActionOptionArray;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getRequestOrderItems()
    {
        if ($this->orderItemCollection === null) {
            $this->orderItemCollection = $this->orderItemCollectionFactory->create()
                ->addFieldToFilter('order_id', ['eq' => $this->getOrderId()])
                ->addFieldToFilter('product_type', ['nin' => $this->orderHelper->getNotReturnedOrderItemProductTypes()])
            ;
            $requestItems = $this->getRequestItems();
            if ($requestItems) {
                $this->orderItemCollection->addFieldToFilter('item_id', ['in' => array_keys($requestItems)]);
            }
        }
        return $this->orderItemCollection;
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
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
