<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;

class CustomerRepositoryInterface
{
    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProvider
     */
    protected $_attributeMetadataDataProvider;
    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionFactory
     */
    private $optionFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    public function __construct(
        \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $optionFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->_universalFactory = $universalFactory;
        $this->_eavConfig = $eavConfig;
        $this->optionFactory = $optionFactory;
        $this->state = $state;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param $subject
     * @param $customer
     * @param null $passwordHash
     * @return array
     */
    public function beforeSave($subject, CustomerInterface $customer, $passwordHash = null)
    {
        if ($customer->getCustomAttributes()) {
            $entityType = $this->_eavConfig->getEntityType('customer');
            $attributes = $this->_universalFactory->create(
                $entityType->getEntityAttributeCollection()
            )->setEntityTypeFilter(
                $entityType
            )->addFieldToFilter('type_internal', 'selectgroup')
                ->getData();
            $groupId = $customer->getGroupId();
            foreach ($attributes as $attribute) {
                $shouldSetCustomerAttribute = false;
                if ($this->state->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                    /*change group id by customer attribute*/
                    $data = $customer->getCustomAttribute($attribute['attribute_code']);

                    if ($data
                        && $data->getValue()
                        && ($this->_scopeConfig->getValue('amcustomerattr/general/allow_change_group')
                            || !$customer->getId()
                        )
                    ) {
                        $option = $this->optionFactory->create()->load($data->getValue());
                        $gr = $option->getGroupId();
                        if ($gr) {
                            $customer->setGroupId($option->getGroupId());
                        }
                    } else {
                        /* fix issue with disabled customer attribute*/
                        $shouldSetCustomerAttribute = true;
                    }
                }

                if ($this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
                    || $shouldSetCustomerAttribute
                ) {
                    if ($groupId) {
                        /*set customer group id from backend to customer group attribute*/
                        $option = $this->optionFactory->create()->load($groupId, 'group_id');
                        if ($option && $option->getOptionId()) {
                            $customer->setCustomAttribute($attribute['attribute_code'], $option->getOptionId());
                        }
                    }
                }
            }
        }

        return [$customer, $passwordHash];
    }
}
