<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

/**
 * Class Request
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class Request extends AbstractResource
{
    /**
     * Sets start value of request increment ID
     */
    const INCREMENT_ID_OFFSET = 0;

    /**
     * @var array
     */
    protected $_serializableFields = [
        'print_label' => [[], []]
    ];

    /**
     * @var \Aheadworks\Rma\Model\RequestItemFactory
     */
    private $requestItemFactory;

    /**
     * @var string
     */
    protected $customFieldTableName = 'aw_rma_request_custom_field_value';

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Aheadworks\Rma\Model\RequestItemFactory $requestItemFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Rma\Model\RequestItemFactory $requestItemFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->requestItemFactory = $requestItemFactory;
    }

    protected function _construct()
    {
        $this->_init('aw_rma_request', 'id');
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $mainTable = $this->getMainTable();
        $conditions = [
            "{$mainTable}.status_id = frontend_label.status_id",
            "{$mainTable}.store_id = frontend_label.store_id",
            "attribute_code = 'frontend_label'"
        ];
        $select
            ->joinLeft(
                ['frontend_label' => $this->getTable('aw_rma_status_attr_value')],
                implode(' AND ', $conditions),
                [
                    'status_frontend_label' => 'frontend_label.value'
                ]
            );
        return $select;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $now = date(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT, time());
        if (!$object->getId()) {
            $object->setCreatedAt($now);
        }
        $object->setUpdatedAt($now);
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveRequestItems($object);
        $this->updateCustomFieldValues($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachCustomFieldValues($object);
        $this->attachIncrementId($object);
        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function attachIncrementId(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setIncrementId(sprintf("#%'09u", $object->getId() + self::INCREMENT_ID_OFFSET));
        return $this;
    }

    /**
     * Save request items
     *
     * @param \Aheadworks\Rma\Model\Request $object
     * @return $this
     */
    private function saveRequestItems(\Aheadworks\Rma\Model\Request $object)
    {
        $items = $object->getItems();
        if (!is_array($items)) {
            return $this;
        }

        if (count($object->getItemsCollection(true))) {
            // algorithm for existing request - saving custom fields
            foreach ($items as $itemId => $data) {
                $requestItem = $this->requestItemFactory->create()->load($itemId);
                $customFields = array_replace($requestItem->getData('custom_fields'), $data['custom_fields']);
                $requestItem->setData('custom_fields', $customFields)->save();
            }
        }
        else { // algorithm for new request
            foreach ($items as $data) {
                $this->requestItemFactory->create()
                    ->setItemId($data['item_id'])
                    ->setRequestId($object->getId())
                    ->addData($data)
                    ->save()
                ;
            }
        }
        return $this;
    }
}
