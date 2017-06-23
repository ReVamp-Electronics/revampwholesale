<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Controller\Adminhtml\Attribute;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Result\PageFactory;

class Update extends \Amasty\CustomerAttributes\Controller\Adminhtml\Attribute
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $optionFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $connection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        GroupRepositoryInterface $groupRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Model\Entity\Attribute\OptionFactory $optionFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->optionFactory = $optionFactory;
        $this->connection = $connection;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->_objectManager->create('Magento\Customer\Model\Attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('We can\'t load the attribute.'));
                return $resultRedirect->setPath('*/*/');
            }

            $options = $this->_attrOptionCollectionFactory->create()->setAttributeFilter(
                $model->getId()
            )->setPositionOrder(
                'asc',
                true
            )->load();

            $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            array_shift($customerGroups);

            $customerGroupsInfo = [];
            foreach ($customerGroups as $group) {
                $customerGroupsInfo[$group->getId()] = $group->getCode();
            }

            foreach ($options as $option) {
                $groupId = $option->getGroupId();
                if ($groupId && array_key_exists($groupId, $customerGroupsInfo)) {
                    unset($customerGroupsInfo[$groupId]);
                }
            }

            try {
                foreach ($customerGroupsInfo as $groupId => $value) {
                    $this->_saveOption($groupId, $value, $model);
                }

                $this->messageManager->addSuccess(__('Customer attribute options was successfully updated'));
                $resultRedirect->setPath('*/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
                return $resultRedirect;

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('*/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to update.'));
        return $resultRedirect->setPath('catalog/*/');
    }

    protected function _saveOption($groupId, $value, $attribute)
    {
        $option = $this->optionFactory->create();
        $option->setAttributeId($attribute->getId());
        $option->setSortOrder($groupId);
        $option->setValue($value);
        $option->setLabel($value);
        $option->setGroupId($groupId);

        $option->save();
        $this->_updateAttributeOptionValues($option->getId(), $value);
    }

    protected function _updateAttributeOptionValues($optionId, $values)
    {
        $connection = $this->connection->getConnection();
        $table = $this->connection->getTableName('eav_attribute_option_value');

        $connection->delete($table, ['option_id = ?' => $optionId]);

        $stores = $this->_storeManager->getStores(true);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            $data = ['option_id' => $optionId, 'store_id' => $storeId, 'value' => $values];
            $connection->insert($table, $data);
        }
    }

}
