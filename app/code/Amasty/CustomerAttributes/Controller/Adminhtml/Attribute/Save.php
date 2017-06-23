<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Controller\Adminhtml\Attribute;

use Amasty\CustomerAttributes\Model\CustomerFormManager;
use Magento\Customer\Api\GroupManagementInterface;

class Save extends \Amasty\CustomerAttributes\Controller\Adminhtml\Attribute
{
    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection $connection
     * @todo delete from here
     */
    protected $connection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    private $groupListFactory;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    private $universalFactory;

    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Amasty\CustomerAttributes\Helper\Image $imageHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Framework\App\ResourceConnection $connection,
        \Amasty\CustomerAttributes\Helper\Image $imageHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupListFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        GroupManagementInterface $groupManagement
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
        $this->connection = $connection;
        $this->attributeFactory = $attributeFactory;
        $this->_imageHelper = $imageHelper;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->attributeManagement = $attributeManagement;
        $this->eavConfig = $eavConfig;
        $this->groupListFactory = $groupListFactory;
        $this->universalFactory = $universalFactory;
        $this->groupManagement = $groupManagement;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->messageManager->addErrorMessage(__('You can not update this attribute'));
            return $this->_redirect('*/*/', ['_current' => true]);
        }
        $this->_session->setAttributeData($data);
        $id = isset($data['attribute_id'])? $data['attribute_id']: null;
        /** @var $model \Magento\Customer\Model\Attribute */
        $model = $this->attributeFactory->create();

        if ($id) {
            $model->load($id);

            /* entity type check */
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('You can not update this attribute'));
                return $this->_redirect('*/*/', ['_current' => true]);
            }

            $data['attribute_code'] = $model->getAttributeCode();
            $data['is_user_defined'] = $model->getIsUserDefined();
            $data['frontend_input'] = $model->getFrontendInput();
            $data['type_internal']  = $model->getTypeInternal();
        } else {
            if (!$this->validateGroupAttribute($data)) {
                $this->messageManager->addErrorMessage(
                    __('Attribute with "Customer Group Selector" type already exists.')
                );
                $resultRedirect->setPath('*/*/new');
                return $resultRedirect;
            }

            $model->setEntityTypeId($this->_entityTypeId);
            $model->setIsUserDefined(1);
        }

        $data = $this->validateData($data, $model);
        $data = $this->setSourceModel($data);
        $this->_session->setAttributeData($data);
        $model->addData($data);
        $model->setData('used_in_forms', $this->getUsedFroms($model));

        $isNewCustomerGroupOptions = $this->_addOptionsForCustomerGroupAttribute($model);
        try {
            $this->_prepareForSave();
            $this->_eventManager->dispatch('amasty_customer_attributes_before_save', ['object' => $model]);
            $model->save();
            $this->_saveDefaultValue($model, $data);

            if ($isNewCustomerGroupOptions) {
                $this->_saveCustomerGroupIds($model);
            }

            if (!$id) {
                $attributeSetId = $this->eavConfig->getEntityType('customer')
                    ->getDefaultAttributeSetId();
                /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $collection */
                $collection = $this->groupListFactory->create();
                $collection->setAttributeSetFilter($attributeSetId);
                $collection->addFilter('attribute_group_code', 'general');

                $this->attributeManagement->assign(
                    'customer',
                    $attributeSetId,
                    $collection->getFirstItem()->getId(),
                    $model->getAttributeCode(),
                    null
                );
            }
            $this->_eventManager->dispatch('customer_attributes_after_save', ['object' => $model]);
            $this->messageManager->addSuccessMessage(__('Customer attribute was successfully saved'));

            $this->_session->setAttributeData(false);
            if ($model->getId()) {
                $resultRedirect->setPath('*/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
            } else {
                $resultRedirect->setPath('*/*/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('amcustomerattr/*/edit', ['attribute_id' => $id, '_current' => true]);
        }

        return $resultRedirect;
    }

    private function validateData($data, \Magento\Customer\Model\Attribute $model)
    {
        if ($data['is_used_in_grid']) {
            $data['is_visible_in_grid']
                = $data['is_filterable_in_grid']
                = $data['is_searchable_in_grid']
                = $data['is_filterable_in_search'] = 1;
        }

        $data['is_configurable'] = isset($data['is_configurable']) ? $data['is_configurable'] : 0;

        $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);

        if (!$defaultValueField && 'statictext' == $data['frontend_input']) {
            $defaultValueField = 'default_value_textarea';
        }

        if ($defaultValueField) {
            $data['default_value'] = $data[$defaultValueField];
        }

        if ($data['is_required'] == CustomerFormManager::REQUIRED_ON_FRONT) {
            $data['required_on_front'] = 1;
            $data['is_required'] = 0;
        } else {
            $data['required_on_front'] = 0;
        }

        if ($model->getIsUserDefined() === null || $model->getIsUserDefined() != 0) {
            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        }

        if (!isset($data['apply_to'])) {
            $data['apply_to'] = [];
        }

        if (!empty($data['customer_groups'])) {
            $data['customer_groups'] = implode(',', $data['customer_groups']);
        } else {
            $data['customer_groups'] = '';
        }

        $data['store_ids'] = '';
        $data['sort_order'] = $data['sorting_order'] + CustomerFormManager::ORDER_OFFSET;//move attributes to the bottom

        if ($data['stores']) {
            if (is_array($data['stores'])) {
                $data['store_ids'] = implode(',', $data['stores']);
            } else {
                $data['store_ids'] = $data['stores'];
            }
            unset($data['stores']);
        }

        return $data;
    }

    /**
     *  System can have only one customer group attribute
     * @param $data
     * @return bool
     */
    private function validateGroupAttribute($data)
    {
        if ('selectgroup' === $data['frontend_input']) {
            $entityType = $this->eavConfig->getEntityType('customer');
            $attributes = $this->universalFactory->create(
                $entityType->getEntityAttributeCollection()
            )->setEntityTypeFilter(
                $entityType
            )->addFieldToFilter('type_internal', 'selectgroup')
                ->getData();
            if (count($attributes)) {
                return false;
            }
        }

        return true;
    }

    protected function _addOptionsForCustomerGroupAttribute(&$model)
    {
        $data = $model->getData();
        if (( (array_key_exists('type_internal', $data) && $data['type_internal'] == 'selectgroup')
            || (array_key_exists('frontend_input', $data) && $data['frontend_input'] == 'selectgroup')
            )
            && !array_key_exists('option', $data)
        ) {
            $values = [
                'order' => [],
                'value' => []
            ];
            $customerGroups =  $this->groupManagement->getLoggedInGroups();
            $i = 0;
            foreach ($customerGroups as $item) {
                $name = 'option_' . ($i++);
                $values['value'][$name] = [
                    0 => $item->getCode()
                ];
                $values['order'][$name] = $item->getId();
                $values['group_id'][$name] = $item->getId();
            }

            $data['option'] = $values;
            $model->setData($data);

            return true;
        }
        return false;
    }

    protected function getUsedFroms($attribute)
    {
        $usedInForms = [
            'adminhtml_customer',
            'amasty_custom_attribute'
        ];
        if ($attribute->getIsVisibleOnFront() == '1') {
            $usedInForms[] = 'customer_account_edit';
        }
        if ($attribute->getOnRegistration() == '1') {
            $usedInForms[] = 'customer_account_create';
            $usedInForms[] = 'customer_attributes_registration';
        }
        if ($attribute->getUsedInProductListing()) {
            $usedInForms[] = 'adminhtml_checkout';
            $usedInForms[] = 'customer_attributes_checkout';

        }

        return $usedInForms;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function setSourceModel($data)
    {
        if (array_key_exists('type_internal', $data)
            && $data['type_internal'] == 'selectgroup') {
            $data['frontend_input'] = 'selectgroup';
        }
        switch ($data['frontend_input']) {
            case 'boolean':
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                break;
            case 'multiselectimg':
            case 'selectimg' :
                $data['data_model'] =
                    'Amasty\CustomerAttributes\Model\Eav\Attribute\Data\\' . ucfirst($data['frontend_input']);
                $data['backend_type'] = 'varchar';
            case 'select':
            case 'checkboxes':
            case 'multiselect':
            case 'radios':
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                $data['backend_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                break;
            case 'file':
                $data['type_internal'] = 'file';
                $data['backend_type'] = 'varchar';
                break;
            case 'statictext':
                $data['type_internal'] = 'statictext';
                $data['backend_type'] = 'text';
                $data['data_model'] =
                    'Amasty\CustomerAttributes\Model\Eav\Attribute\Data\\' . ucfirst($data['frontend_input']);
                break;
            case 'selectgroup':
                $data['type_internal'] = 'selectgroup';
                $data['frontend_input']= 'select';
                $data['backend_type'] = 'varchar';
                $data['source_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                $data['backend_model']
                    = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                break;
        }

        return $data;
    }

    protected function _saveDefaultValue($object, $data)
    {
        if (('multiselectimg' === $data['frontend_input'] || 'selectimg' === $data['frontend_input'])
            && array_key_exists('default', $data)
            && is_array($data['default'])
        ) {
            if ($data['default'] !== null) {
                $bind = ['default_value' => implode(',', $data['default'])];
                $where = ['attribute_id = ?' => $object->getId()];
                $this->connection->getConnection()->update(
                    $this->connection->getTableName('eav_attribute'),
                    $bind,
                    $where
                );
            }
        }
    }

    protected function _saveCustomerGroupIds($model)
    {
        $data = $model->getData();
        if ($data['type_internal'] == 'selectgroup'
            || $data['frontend_input'] == 'selectgroup'
        ) {
            $options = $this->_attrOptionCollectionFactory->create()->setAttributeFilter(
                $model->getId()
            )->setPositionOrder(
                'asc',
                true
            )->load();

            $customerGroups = $this->groupManagement->getLoggedInGroups();
            $i = 1;
            foreach ($options as $option) {
                if (array_key_exists($i, $customerGroups)) {
                    $group = $customerGroups[$i++];
                    if ($group->getCode() == $option->getValue()) {
                        $option->setGroupId($group->getId());
                        $option->save();
                    }
                }
            }
        }
    }

    protected function _prepareForSave()
    {
        /** Deleting */
        $toDelete = $this->getRequest()->getParam('amcustomerattr_icon_delete');
        if ($toDelete) {
            foreach ($toDelete as $optionId => $del) {
                if ($del) {
                    $this->_imageHelper->delete($optionId);
                }
            }
        }

        /* Uploading files */
        $files = $this->getRequest()->getFiles('amcustomerattr_icon');
        if ($files) {
            foreach ($files as $optionId => $file) {
                if (UPLOAD_ERR_OK == $file['error']) {
                    $this->_imageHelper->uploadImage($optionId);
                }
            }
        }
    }
}
