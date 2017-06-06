<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\ResourceModel;

/**
 * Class ThreadMessage
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class ThreadMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Aheadworks\Rma\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Aheadworks\Rma\Model\AttachmentFactory $attachmentFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Rma\Model\AttachmentFactory $attachmentFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->attachmentFactory = $attachmentFactory;
    }

    protected function _construct()
    {
        $this->_init('aw_rma_thread_message', 'id');
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
        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveAttachments($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachAttachmentsData($object);
        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    private function saveAttachments(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!is_array($object->getAttachment())) {
            return $this;
        }
        foreach ($object->getAttachment() as $attachmentData) {
            /** @var \Aheadworks\Rma\Model\Attachment $attachment */
            $attachment = $this->attachmentFactory->create();
            $attachment
                ->setData($attachmentData)
                ->setMessageId($object->getId())
                ->save()
            ;
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function attachAttachmentsData(\Magento\Framework\Model\AbstractModel $object)
    {
        $attachmentsData = [];
        $connection = $this->getConnection();
        $columns = [
            'id' => 'id',
            'name' => 'name',
            'length' => new \Zend_Db_Expr('length(content)')
        ];
        $select = $connection->select()
            ->from($this->getTable('aw_rma_thread_attachment'), $columns)
            ->where("message_id = ?", $object->getId())
        ;
        foreach ($connection->fetchAll($select) as $data) {
            $attachmentsData[] = $data;
        }
        $object->setAttachments($attachmentsData);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function attachOwnerName(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $tableName = $object->isAdmin() ? 'admin_user' : 'customer_entity';
        $columns = [
            'firstname' => 'firstname',
            'lastname' => 'lastname'
        ];
        $idFieldName = $object->isAdmin() ? 'user_id' : 'entity_id';
        $select = $connection->select()
            ->from($this->getTable($tableName), $columns)
            ->where("{$idFieldName} = ?", $object->getOwnerId())
        ;
        if ($result = $connection->fetchRow($select)) {
            $object->setOwnerName($result['firstname'] . ' ' . $result['lastname']);
        }
        return $this;
    }

    /**
     * @return \Magento\Framework\Validator\DataObject|null
     */
    public function getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();

        $nameNotEmpty = new \Zend_Validate_NotEmpty();
        $nameNotEmpty->setMessage(__('Message text is required.'), \Zend_Validate_NotEmpty::IS_EMPTY);
        $validator->addRule($nameNotEmpty, 'text');

        return $validator;
    }
}
