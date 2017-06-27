<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Controller\Adminhtml\Index;

class InlineEdit extends \Amasty\Pgrid\Controller\Adminhtml\Index
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $attributes;
    protected $logger;
    protected $factory;
    protected $helper;
    protected $skipAttributeUpdate = ['sku'];
    protected $storeManager;
    protected $store;
    protected $stockRegistry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Pgrid\Ui\Component\Listing\Attribute\Repository $attributeRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Element\UiComponentFactory $factory,
        \Amasty\Pgrid\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;

        $this->logger = $logger;
        $this->factory = $factory;
        $this->helper = $helper;

        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;

        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('amastyItems', []);
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $this->store = $this->storeManager->getStore($storeId);

        foreach ($postItems as $productId => $postData) {

//            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            $product = $this->productRepository->getById($productId, true, $storeId);


            if ($product->getId()){
                $this->updateProduct($product, $postData);
                $this->saveProduct($product);
            }

        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists(),
            'grid' => $this->getGridData()
        ]);
    }

    protected function getGridData()
    {
        $grid = '';
        if (!$this->isErrorExists()){
            $component = $this->factory->create($this->_request->getParam('namespace'));
            $this->prepareComponent($component);
            $grid = \Zend_Json::decode($component->render());
        }

        return $grid;
    }

    protected function getAttributes()
    {
        if (!$this->attributes){
            $this->attributes = [];
            foreach($this->attributeRepository->getList() as $attribute){
                $this->attributes[$attribute->getAttributeCode()] = $this->attributes;
            }
        }
        return $this->attributes;
    }

    protected function _getNumeric($value)
    {
        $operator = null;

        if (strpos($value, '+') !== false){
            $operator = '+';
        } else if (strpos($value, '-') !== false){
            $operator = '-';
        }

        if ($operator){
            $data = explode($operator, $value);
            list($arg1, $arg2) = $data;
            switch($operator){
                case "+":
                    $value = $arg1 + $arg2;
                    break;
                case "-":
                    $value = $arg1 - $arg2;
                    break;
            }
        }

        return $value;
    }

    protected function setData(\Magento\Catalog\Api\Data\ProductInterface $product, $key, $val)
    {
        if (is_array($this->getAttributes()) && in_array($key, array_keys($this->getAttributes()))) {
            if (is_array($val)){
                $val = implode(',', $val);
            }

            if (!in_array($key, $this->skipAttributeUpdate)){
                $product->addAttributeUpdate($key, $val, $this->store);
            }
            $product->setData($key, $val);
        } else if ($key == 'qty'){
            $quantityAndStockStatus = $product->getData('quantity_and_stock_status');
            $qtyBefore = $quantityAndStockStatus[$key];
            $quantityAndStockStatus[$key] = $this->_getNumeric($val);
            $qtyAfter = $quantityAndStockStatus[$key];

            if ($this->helper->getModuleConfig('modification/availability')) {

                if ($qtyBefore > 0 && $qtyAfter <= 0) {
                    $quantityAndStockStatus['is_in_stock'] = 0;
                }
                if ($qtyBefore <= 0 && $qtyAfter > 0) {
                    $quantityAndStockStatus['is_in_stock'] = 1;
                }
            }

            $product->setData('quantity_and_stock_status', $quantityAndStockStatus);

        } else if ($key == 'amasty_availability'){
            $quantityAndStockStatus = $product->getData('quantity_and_stock_status');
            $quantityAndStockStatus['is_in_stock'] = $val;
            $product->setData('quantity_and_stock_status', $quantityAndStockStatus);
        } else {
            $product->setData($key, $val);
        }
    }

    protected function updateProduct(\Magento\Catalog\Api\Data\ProductInterface $product, array $data)
    {
        foreach($data as $key => $val){
            if ($product->getData($key) != $val || $product->getData($key) === null){
                $this->setData($product, $key, $val);
            }
        }
    }

    protected function saveProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        try {
            $inventory = $product->getData('quantity_and_stock_status');

            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $this->store->getWebsiteId());

            if ($inventory) {
                if ($stockItem->getIsInStock() != $inventory['is_in_stock'] || $stockItem->getQty() != $inventory['qty']) {
                    $stockItem
                        ->setIsInStock($inventory['is_in_stock'])
                        ->setQty($inventory['qty']);

                    $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
                }
            }

            $product->save();
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()->addError($this->getErrorWithProductId($product, $e->getMessage()));
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addError($this->getErrorWithProductId($product, $e->getMessage()));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()->addError($this->getErrorWithProductId($product, 'We can\'t save the product.'));
            $this->logger->critical($e);
        }
    }

    protected function getErrorWithProductId(\Magento\Catalog\Api\Data\ProductInterface $product, $errorText)
    {
        return '[Product ID: ' . $product->getId() . '] ' . __($errorText);
    }

    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }

    protected function prepareComponent(\Magento\Framework\View\Element\UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
