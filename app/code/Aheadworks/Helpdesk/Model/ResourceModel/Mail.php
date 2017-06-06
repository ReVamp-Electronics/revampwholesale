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
class Mail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Attachment collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment\CollectionFactory
     */
    protected $attachmentCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Mail\Attachment\CollectionFactory $attachmentCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment\CollectionFactory $attachmentCollectionFactory
    ) {
        $this->attachmentCollectionFactory = $attachmentCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_helpdesk_gateway_mail', 'id');
    }

    /**
     * @param string $messageUid
     *
     * @return bool
     */
    public function isMailExistByMailUid($messageUid)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from(['gm' => $this->getMainTable()], 'COUNT(id)')
            ->where('gm.uid = ?', $messageUid)
        ;
        return (bool)$adapter->fetchOne($select->__toString());
    }

    /**
     * @param int $gatewayId
     *
     * @return array
     */
    public function getExistMailUIDs($gatewayEmail)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()
            ->from(['gm' => $this->getMainTable()], "REPLACE(gm.uid, '{$gatewayEmail}', '')")
            ->where('gm.gateway_email = ?', $gatewayEmail)
        ;
        return $adapter->fetchCol($select->__toString());
    }

    /**
     * Get attachment collection
     *
     * @return Mail\Attachment\Collection
     */
    public function getAttachmentCollection()
    {
        /** @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Attachment\Collection $attachmentCollection */
        $attachmentCollection = $this->attachmentCollectionFactory->create();
        return $attachmentCollection;
    }
}