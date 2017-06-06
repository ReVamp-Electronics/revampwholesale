<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel;

/**
 * Class ThreadMessage
 * @package Aheadworks\Helpdesk\Model\ResourceModel
 */
class ThreadMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Attachment factory
     * @var \Aheadworks\Helpdesk\Model\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * Attachment resource
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Attachment
     */
    protected $attachmentResource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Aheadworks\Helpdesk\Model\AttachmentFactory $attachmentFactory
     * @param Attachment $attachmentResource
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Helpdesk\Model\AttachmentFactory $attachmentFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Attachment $attachmentResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResource = $attachmentResource;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_ticket_message', 'id');
    }

    /**
     * Before save method
     *
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
     * After save method
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveAttachments($object);
        return parent::_afterSave($object);
    }

    /**
     * After load method
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->attachAttachmentsData($object);
        return parent::_afterLoad($object);
    }

    /**
     * Save attachment
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    private function saveAttachments(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!is_array($object->getAttachment())) {
            return $this;
        }
        foreach ($object->getAttachment() as $attachmentData) {
            /** @var \Aheadworks\Helpdesk\Model\Attachment $attachment */
            $attachment = $this->attachmentFactory->create();
            unset($attachmentData['id']);
            $attachment
                ->setData($attachmentData)
                ->setMessageId($object->getId())
            ;
            $this->attachmentResource->save($attachment);
        }
        return $this;
    }

    /**
     * Set attachment data
     *
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
            ->from($this->getTable('aw_helpdesk_attachment'), $columns)
            ->where("message_id = ?", $object->getId())
        ;
        foreach ($connection->fetchAll($select) as $data) {
            $attachmentsData[] = $data;
        }
        $object->setAttachments($attachmentsData);

        return $this;
    }

    /**
     * Attach author name
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    public function attachAuthor(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isSystem()) {
            $object->setAuthorName(__('System'));
            return $this;
        }
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
            $object->setAuthorName($result['firstname'] . ' ' . $result['lastname']);
            $object->setAuthorEmail($result['email']);
        }
        return $this;
    }

    /**
     * Get validation rules before save
     *
     * @return \Magento\Framework\Validator\DataObject|null
     */
    public function getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();
        return $validator;
    }
}
