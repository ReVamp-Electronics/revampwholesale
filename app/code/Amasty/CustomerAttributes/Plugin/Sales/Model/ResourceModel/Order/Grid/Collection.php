<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Sales\Model\ResourceModel\Order\Grid;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Collection
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->customerRepository = $customerRepository;
        $this->_objectManager = $objectManager;
    }

    public function beforeAddItem(
        $subject,
        $item
    ) {
        $customerId = $item->getCustomerId();
        if ($customerId > 0) {
            $customerAttributes = false;
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customerAttributes = $customer->getCustomAttributes();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $customerId = 0;//customer was deleted
            }

            if ($customerAttributes) {
                foreach ($customerAttributes as $customerAttribute) {
                    $value = $customerAttribute->getValue();
                    if ($value) {
                        $name = $customerAttribute->getAttributeCode();
                        $item->setData($name, $value);
                    }
                }
            }
        } else {
            $model = $this->_objectManager
                ->create('Amasty\CustomerAttributes\Model\Customer\GuestAttributes')
                ->loadByOrderId($item->getEntityId());
            if ($model && $model->getId()) {
                foreach ($model->getData() as $key => $value) {
                    if ($key == 'id') {
                        continue;
                    }
                    if ($value) {
                        $name = $key;
                        $item->setData($name, $value);
                    }
                }
            }
        }

        return  [$item];
    }
}
