<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentSearchResultsInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;
use Aheadworks\Helpdesk\Model\Source\Gateway\Protocol as ProtocolSource;

/**
 * Class CreateMail
 * @package Aheadworks\Helpdesk\Cron
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateMail
{
    /**
     * Cron run interval.
     */
    const RUN_INTERVAL = 300;

    /**
     * Mail storage
     * @var null | \Zend_Mail_Storage_Imap | \Zend_Mail_Storage_Pop3
     */
    private $connection;

    /**
     * Mail resource model
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail
     */
    private $mailResource;

    /**
     * Mail model factory
     * @var \Aheadworks\Helpdesk\Model\MailFactory
     */
    private $mailFactory;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Current gateway
     * @var DepartmentGatewayInterface
     */
    private $currentGateway;

    /**
     * DateTime lib
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Config model
     * @var \Aheadworks\Helpdesk\Model\Config
     */
    private $hduConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Mail $mailResource
     * @param \Aheadworks\Helpdesk\Model\Gateway $gatewayModel
     * @param \Aheadworks\Helpdesk\Model\MailFactory $mailFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Helpdesk\Model\Config $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail $mailResource,
        \Aheadworks\Helpdesk\Model\Gateway $gatewayModel,
        \Aheadworks\Helpdesk\Model\MailFactory $mailFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Helpdesk\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DepartmentRepositoryInterface $departmentRepository
    ) {
        $this->mailResource = $mailResource;
        $this->gatewayModel = $gatewayModel;
        $this->mailFactory = $mailFactory;
        $this->storeManager = $storeManager;
        $this->hduConfig = $config;
        $this->dateTime = $dateTime;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->departmentRepository = $departmentRepository;
        return $this;
    }

    /**
     * Create mails
     * @return $this
     */
    public function execute()
    {
        if ($this->isLocked(
            $this->hduConfig->getParam(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_MAIL),
            self::RUN_INTERVAL
        )) {
            return $this;
        }

        $this->searchCriteriaBuilder
            ->addFilter(DepartmentInterface::IS_ENABLED, true);

        /** @var DepartmentSearchResultsInterface $result */
        $result = $this->departmentRepository->getList($this->searchCriteriaBuilder->create());

        if ($result->getTotalCount()) {
            /** @var DepartmentInterface[] $departmentList */
            $departmentList = $result->getItems();

            foreach ($departmentList as $department) {
                /** @var DepartmentInterface $departmentDataObject */
                $departmentDataObject = $this->departmentRepository->getById($department->getId());
                /** @var DepartmentGatewayInterface $gatewayDataObject */
                $gatewayDataObject = $departmentDataObject->getGateway();
                if (!$gatewayDataObject || !$gatewayDataObject->getIsEnabled()) {
                    continue;
                }
                $this->connection = $this->gatewayModel->getConnection($gatewayDataObject);
                if ($this->connection) {
                    $this->currentGateway = $gatewayDataObject;
                    $this->prepareAndSaveMail();
                }
            }
        }
        $this->setLastExecTime(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_MAIL);
        return $this;
    }

    /**
     * Prepare mail and save
     * @return $this
     */
    private function prepareAndSaveMail()
    {
        try {
            $mailboxUIDs = $this->getNewMessageUids();
        } catch (\Exception $e) {
            $mailboxUIDs = [];
        }
        if (count($mailboxUIDs) > 0) {
            foreach ($mailboxUIDs as $messageUid) {
                $message = $this->getMessageByUid($messageUid);
                $this->convertMessageToMail($message, $messageUid);
            }
        }
        return $this;
    }

    /**
     * Get new messages
     * @return array
     */
    protected function getNewMessageUids()
    {
        //message uids from email gateway
        $mailboxUIDs = $this->connection->getUniqueId();
        //already saved message uids
        $unExistUIDs = $this->getUnExistMessageUIDs($mailboxUIDs);

        //get only unread messages - for IMAP only
        if ($this->currentGateway->getProtocol() == ProtocolSource::IMAP_VALUE) {
            foreach ($unExistUIDs as $key => $messageUid) {
                $message = $this->getMessageByUid($messageUid);
                if ($message->hasFlag(\Zend_Mail_Storage::FLAG_SEEN) === true) {
                    unset($unExistUIDs[$key]);
                }
            }
        }
        return $unExistUIDs;
    }

    /**
     * Get unsaved messages
     * @param $mailboxUIDs
     * @return array
     */
    protected function getUnExistMessageUIDs($mailboxUIDs)
    {
        $existUIDs = $this->mailResource->getExistMailUIDs($this->currentGateway->getEmail());
        return array_diff($mailboxUIDs, $existUIDs);
    }

    /**
     * Is mail exist
     * @param $messageUid
     * @return bool
     */
    protected function isMailExistByMessageUid($messageUid)
    {
        $mailUid = $this->getMailUidByMessageUid($messageUid);
        return $this->mailResource->isMailExistByMailUid($mailUid);
    }

    /**
     * Get message by UID
     * @param $uid
     * @return \Zend_Mail_Message
     */
    protected function getMessageByUid($uid)
    {
        return $this->getMessageByNumber($this->getMessageNumberByUid($uid));
    }

    /**
     * Get message number by UID
     * @param $uid
     * @return int
     */
    protected function getMessageNumberByUid($uid)
    {
        return $this->connection->getNumberByUniqueId($uid);
    }

    /**
     * Get message by number
     * @param $number
     * @return \Zend_Mail_Message
     */
    protected function getMessageByNumber($number)
    {
        return $this->connection->getMessage($number);
    }

    /**
     * Convert message to mail
     * @param $message
     * @param $messageUid
     * @return $this
     */
    protected function convertMessageToMail($message, $messageUid)
    {
        $messageNumber = $this->getMessageNumberByUid($messageUid);
        $currentDate = new \DateTime();

        /** @var \Aheadworks\Helpdesk\Model\Mail $mail */
        $mail = $this->mailFactory->create();
        $mail
            ->setUid($this->getMailUidByMessageUid($messageUid))
            ->setGatewayEmail($this->currentGateway->getEmail())
            ->setFrom($this->getMessageFrom($message))
            ->setTo($this->getMessageTo($message))
            ->setType(\Aheadworks\Helpdesk\Model\Mail::TYPE_FROM_GATEWAY)
            ->setBody($this->getMessageBody($message))
            ->setHeaders($this->getMessageHeadersByNumber($messageNumber))
            ->setSubject($this->getMessageSubject($message))
            ->setContentType(strtok($this->getMessageContentType($message), ';'))
            ->setCreatedAt($currentDate->format('Y-m-d H:i:s'))
            ->setStatus(\Aheadworks\Helpdesk\Model\Mail::STATUS_UNPROCESSED)
            ->setStoreId($this->currentGateway->getDefaultStoreId())
        ;
        $this->mailResource->save($mail);

        $attachments = $this->getMessageAttachment($message);
        if (is_array($attachments)) {
            foreach ($attachments as $attach) {
                if (is_array($attach)) {
                    $mail->addAttachmentFromArray($attach);
                }
            }
        }
        //remove mail from gateway
        if ($this->currentGateway->getIsDeleteParsed()) {
            $this->removeMessageFromServerByNumber($messageNumber);
        }
        return $this;
    }

    /**
     * Get message content type
     * @param $message
     * @return string
     */
    protected function getMessageContentType($message)
    {
        $part = $this->getMainPart($message);
        try {
            $headers = $part->getHeaders();
            $contentType = $headers['content-type'] ? $headers['content-type']
                : \Zend_Mime::TYPE_TEXT;
        } catch (\Exception $e) {
            $contentType = \Zend_Mime::TYPE_TEXT;
        }
        return $contentType;
    }

    /**
     * Get message body
     * @param \Zend_Mail_Message $message
     * @return string
     */
    protected function getMessageBody($message)
    {
        // Get first flat part
        $part = $this->getMainPart($message);

        $headers = $part->getHeaders();
        $encodedContent = $part->getContent();

        if (!isset($headers['content-transfer-encoding'])) {
            $content = $encodedContent;
        } else {
            // Decoding transfer-encoding
            switch (strtolower($headers['content-transfer-encoding'])) {
                case \Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                    $content = quoted_printable_decode($encodedContent);
                    break;
                case \Zend_Mime::ENCODING_BASE64:
                    $content = base64_decode($encodedContent);
                    break;
            }
        }

        $contentType = $this->getMessageContentType($message);
        foreach (explode(";", $contentType) as $headerPart) {
            $headerPart = strtolower(trim($headerPart));
            if (strpos($headerPart, 'charset=') !== false) {
                $charset = preg_replace('/charset=[^a-z0-9\-_]*([a-z\-_0-9]+)[^a-z0-9\-]*/i', "$1", $headerPart);
                return iconv($charset, 'UTF-8', $content);
            }
        }
        return $content;
    }

    /**
     * Get subject
     * @param \Zend_Mail_Message $message
     * @return string
     */
    protected function getMessageSubject($message)
    {
        $subject = false;
        if ($message->headerExists('subject')) {
            $headers = $message->getHeaders();
            $encodedContent = $message->subject;
            if (!isset($headers['content-transfer-encoding'])) {
                $content = $encodedContent;
            } else {
                // Decoding transfer-encoding
                switch (strtolower($headers['content-transfer-encoding'])) {
                    case \Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                        $content = quoted_printable_decode($encodedContent);
                        break;
                    case \Zend_Mime::ENCODING_BASE64:
                        $content = base64_decode($encodedContent);
                        break;
                }
            }
            $subject = $content;
        }

        if (!$subject) {
            $subject = __('No Subject');
        }
        return $subject;
    }

    /**
     * Get from
     * @param \Zend_Mail_Message $message
     * @return string
     */
    protected function getMessageFrom($message)
    {
        $from = $message->from;
        if (!$from) {
            $from = __('Unknown');
        }
        return $from;
    }

    /**
     * Get to
     * @param $message
     * @return bool|\Magento\Framework\Phrase|string
     */
    protected function getMessageTo($message)
    {
        $to = $this->decodeMimeHeader($message->to);
        if (!$to) {
            $to = __('Unknown');
        }
        return $to;
    }

    /**
     * Decode value
     * @param $value
     * @return bool|string
     */
    protected function decodeMimeHeader($value)
    {
        $encoding = mb_detect_encoding($value, "auto", true);
        if ($encoding === false) {
            try {
                $encoding = iconv_get_encoding();
                $encodedValue = iconv($encoding['internal_encoding'], 'UTF-8', $value);
            } catch (\Exception $e) {
                $encodedValue = false;
            }
        } else {
            $encodedValue = iconv_mime_decode($value, 0, "UTF-8");
        }
        return $encodedValue;
    }

    /**
     * Get headers
     * @param string $number
     * @return string
     */
    protected function getMessageHeadersByNumber($number)
    {
        return $this->connection->getRawHeader($number);
    }

    /**
     * Get attachment
     * @param \Zend_Mail_Message $message
     * @return array('filename' => $filename, 'content' => $content) | false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getMessageAttachment($message)
    {
        $data = [];

        // Get first flat part
        if ($message->isMultipart()) {
            $parts = $message;
            foreach (new \RecursiveIteratorIterator($parts) as $part) {
                $attach = $this->getMessageAttachment($part);
                if ($attach) {
                    $data[] = $attach;
                }
            }
        } else {
            $headers = $message->getHeaders();
            $isAttachment = null;
            foreach ($headers as $value) {
                if (is_array($value)) {
                    $value = implode(";", $value);
                }
                if ($isAttachment = preg_match('/(name|filename)="{0,1}([^;\"]*)"{0,1}/si', $value, $matches)) {
                    break;
                }
            }
            if ($isAttachment) {
                $filename = $matches[2];
                $encodedContent = $message->getContent();
                if (!isset($headers['content-transfer-encoding'])) {
                    $content = $encodedContent;
                } else {
                    // Decoding transfer-encoding
                    switch (strtolower($headers['content-transfer-encoding'])) {
                        case \Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                            $content = quoted_printable_decode($encodedContent);
                            break;
                        case \Zend_Mime::ENCODING_BASE64:
                            $content = base64_decode($encodedContent);
                            break;
                    }
                }

                $filename = iconv_mime_decode(
                    $filename,
                    ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
                    'UTF-8'
                );
                return ['filename' => $filename, 'content' => $content];
            }
            return false;
        }
        return $data;
    }

    /**
     * Get mail uid
     * @param string $messageUid
     * @return string
     */
    public function getMailUidByMessageUid($messageUid)
    {
        return $messageUid . $this->currentGateway->getEmail();
    }

    /**
     * Remove message from server
     * @param $number
     * @return $this
     */
    protected function removeMessageFromServerByNumber($number)
    {
        $this->connection->removeMessage($number);
        return $this;
    }

    /**
     * Returns main mail part
     *
     * @param \Zend_Mail_Message $message
     * @return \Zend_Mail_Message
     */
    protected function getMainPart(\Zend_Mail_Message $message)
    {
        // Get first flat part
        $part = $message;
        while ($part->isMultipart()) {
            $part = $part->getPart(1);
        }
        return $part;
    }

    /**
     * Is locked
     * @param $paramName
     * @param $interval
     * @return bool
     */
    protected function isLocked($paramName, $interval)
    {
        $lastExecTime = $this->hduConfig->getParam($paramName);
        $now = $this->dateTime->timestamp();
        return $now < $lastExecTime + $interval;
    }

    /**
     * Set last exec time
     * @param $paramName
     */
    protected function setLastExecTime($paramName)
    {
        $now = $this->dateTime->timestamp();
        $this->hduConfig->setParam($paramName, $now);
    }
}
