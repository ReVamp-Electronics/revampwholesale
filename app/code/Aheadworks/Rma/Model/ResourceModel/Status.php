<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

/**
 * Class Status
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class Status extends AbstractResource
{
    /**
     * @var array
     */
    protected $attributeCodes = [
        'frontend_label',
        'template_to_admin',
        'template_to_customer',
        'template_to_thread'
    ];

    /**
     * @var string
     */
    protected $entityRefFieldName = 'status_id';

    /**
     * @var string
     */
    protected $attrTableName = 'aw_rma_status_attr_value';

    protected function _construct()
    {
        $this->_init('aw_rma_request_status', 'id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $attributeData = $object->getAttribute();
        if (!$object->getIsEmailCustomer()) {
            $object->setIsEmailCustomer(0);
            $attributeData['template_to_customer'] = [];
        }
        if (!$object->getIsEmailAdmin()) {
            $object->setIsEmailAdmin(0);
            $attributeData['template_to_admin'] = [];
        }
        if (!$object->getIsThread()) {
            $object->setIsThread(0);
            $attributeData['template_to_thread'] = [];
        }
        $object->setAttribute($attributeData);

        $this->validateAttributes($object);
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->updateAttributeValues($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachAttributeValues($object);
        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $validators
     * @return $this
     */
    protected function validateAttributes(\Magento\Framework\Model\AbstractModel $object, $validators = [])
    {
        $frontendLabelNotEmpty = new \Zend_Validate_NotEmpty();
        $frontendLabelNotEmpty->setMessage('Frontend Label is required.', \Zend_Validate_NotEmpty::IS_EMPTY);
        $templateToThreadNotEmpty = new \Zend_Validate_NotEmpty();
        $templateToThreadNotEmpty->setMessage('Message to Request Thread is required.', \Zend_Validate_NotEmpty::IS_EMPTY);
        $validators = [
            'frontend_label' => [$frontendLabelNotEmpty],
            'template_to_thread' => [$templateToThreadNotEmpty]
        ];
        return parent::validateAttributes($object, $validators);
    }
}
