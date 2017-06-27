<?php

namespace MW\RewardPoints\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $_catalogProductAction;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \MW\RewardPoints\Model\ProductsellpointFactory
     */
    protected $_productsellpointFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Model\Product\Action $catalogProductAction
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Model\Product\Action $catalogProductAction,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\RewardPoints\Model\ProductsellpointFactory $productsellpointFactory
    ) {
        $this->_request = $request;
        $this->_backendSession = $backendSession;
        $this->_catalogProductAction = $catalogProductAction;
        $this->_logger = $logger;
        $this->_resource = $resource;
        $this->_messageManager = $messageManager;
        $this->_productsellpointFactory = $productsellpointFactory;
    }

    /**
     * Save reward points before saving product
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getParams();
        if (isset($data['mw_reward_point_sell_product']) || isset($data['reward_point_product'])) {
            try {
                if (count($data['mw_reward_point_sell_product']) > 0) {
                    foreach ($data['mw_reward_point_sell_product'] as $key => $value) {
                        if (substr_count($key, '_') == 1) {
                            $product   = explode('mw_', $key);
                            $productId = $product[1];
                            if (!$value || $value == 0) {
                                $value = 0;
                            }
                            $attributesData = ['mw_reward_point_sell_product' => $value];
                            $this->_catalogProductAction->updateAttributes([$productId], $attributesData, 0);
                        } else {
                            if (strpos($key, 'super_attribute') > -1) {
                                $product      = explode('_', $key);
                                $productId    = $product[2];
                                $optionId     = $product[3];
                                $optionTypeId = $product[4];
                                $type         = 'super_attribute';
                            } else {
                                $product      = explode('_', $key);
                                $productId    = $product[1];
                                $optionId     = $product[2];
                                $optionTypeId = $product[3];
                                $type         = 'custom_option';
                            }

                            $collection = $this->_productsellpointFactory->create()->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('option_id', $optionId)
                                ->addFieldToFilter('option_type_id', $optionTypeId)
                                ->addFieldToFilter('type_id', $type)
                                ->getFirstItem();

                            if (is_numeric($value)) {
                                $writeConnection = $this->_resource->getConnection('write');
                                $table           = $this->_resource->getTableName('mw_reward_point_sell_point');

                                if ($collection->getData('id') > 0) {
                                    $query = "UPDATE {$table} SET sell_point = {$value} WHERE product_id = {$productId} AND option_id = {$optionId} AND option_type_id = {$optionTypeId} AND type_id = '{$type}'";
                                } else {
                                    $query = "INSERT INTO {$table} SET sell_point = {$value}, product_id = {$productId}, option_id = {$optionId}, option_type_id = {$optionTypeId}, type_id = '{$type}'";
                                }

                                try {
                                    $writeConnection->query($query);
                                } catch (\Exception $e) {
                                    $this->_logger->addError($e->getMessage());
                                }
                            }
                        }
                    }
                }

                if (count($data['reward_point_product'])) {
                    foreach ($data['reward_point_product'] as $key => $value) {
                        if (!$value || $value == 0) {
                            $value = 0;
                        }
                        if (substr_count($key, '_') == 1) {
                            $product        = explode('mw_', $key);
                            $productId     = $product[1];
                            $attributesData = ['reward_point_product' => $value];

                            $this->_catalogProductAction->updateAttributes([$productId], $attributesData, 0);
                        } else {
                            if (strpos($key, 'super_attribute') > -1) {
                                $product      = explode('_', $key);
                                $productId    = $product[2];
                                $optionId     = $product[3];
                                $optionTypeId = $product[4];
                                $type         = 'super_attribute';
                            } else {
                                $product      = explode('_', $key);
                                $productId    = $product[1];
                                $optionId     = $product[2];
                                $optionTypeId = $product[3];
                                $type         = 'custom_option';
                            }

                            $collection = $this->_productsellpointFactory->create()->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('option_id', $optionId)
                                ->addFieldToFilter('option_type_id', $optionTypeId)
                                ->addFieldToFilter('type_id', $type)
                                ->getFirstItem();

                            $writeConnection = $this->_resource->getConnection('write');
                            $table           = $this->_resource->getTableName('mw_reward_point_sell_point');

                            if ($collection->getData('id') > 0) {
                                $query = "UPDATE {$table} SET earn_point = {$value} WHERE product_id = {$productId} AND option_id = {$optionId} AND option_type_id = {$optionTypeId} AND type_id = '{$type}'";
                            } else {
                                $query = "INSERT INTO {$table} SET earn_point = {$value}, product_id = {$productId}, option_id = {$optionId}, option_type_id = {$optionTypeId}, type_id = '{$type}'";
                            }

                            try {
                                $writeConnection->query($query);
                            } catch (\Exception $e) {
                                $this->_logger->addError($e->getMessage());
                            }
                        }
                    }
                }

                $this->_messageManager->addSuccess(__('The reward points has been saved successfully!'));
                $this->_backendSession->setFormData(false);

                return;
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
                $this->_backendSession->setFormData($data);

                return;
            }
        }
    }
}
