<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Cron;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\DepartmentRepository;

/**
 * Class CreateTicket
 * @package Aheadworks\Helpdesk\Cron
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTicket
{
    /**
     * Cron run interval.
     */
    const RUN_INTERVAL = 300;

    /**
     * Mail limit
     */
    const MAIL_LIMIT = 50;

    /**
     * Mail resource model
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail
     */
    protected $mailResource;

    /**
     * Mail collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail\CollectionFactory
     */
    protected $mailCollectionFactory;

    /**
     * Customer repository
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * TicketFlat repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    protected $ticketDataFactory;

    /**
     * Ticket flat data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory
     */
    protected $ticketFlatDataFactory;

    /**
     * Data object helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * DateTime lib
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Config model
     * @var \Aheadworks\Helpdesk\Model\Config
     */
    protected $hduConfig;

    /**
     * If new ticket followup from old ticket
     * @var bool
     */
    private $followUpTicket = false;

    /**
     * @var DepartmentRepository
     */
    private $departmentRepository;

    /**
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Mail $mailResource
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Mail\CollectionFactory $mailCollection
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     * @param \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource
     * @param \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory
     * @param \Aheadworks\Helpdesk\Model\Config $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param DepartmentRepository $departmentRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail $mailResource,
        \Aheadworks\Helpdesk\Model\ResourceModel\Mail\CollectionFactory $mailCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository,
        \Aheadworks\Helpdesk\Api\Data\TicketFlatInterfaceFactory $ticketFlatInterfaceFactory,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage $threadMessageResource,
        \Aheadworks\Helpdesk\Model\ThreadMessageFactory $threadMessageFactory,
        \Aheadworks\Helpdesk\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        DepartmentRepository $departmentRepository
    ) {
        $this->mailResource = $mailResource;
        $this->mailCollectionFactory = $mailCollection;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->ticketDataFactory = $ticketInterfaceFactory;
        $this->ticketFlatDataFactory = $ticketFlatInterfaceFactory;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageResource = $threadMessageResource;
        $this->hduConfig = $config;
        $this->dateTime = $dateTime;
        $this->departmentRepository = $departmentRepository;
        return $this;
    }

    /**
     * Create tickets from mail
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if ($this->isLocked(
            $this->hduConfig->getParam(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_TICKET),
            self::RUN_INTERVAL
        )) {
            return $this;
        }

        /** @var \Aheadworks\Helpdesk\Model\ResourceModel\Mail\Collection $mailCollection*/
        $mailCollection = $this->mailCollectionFactory->create();

        //get only unprocessed mail from gateway
        $mailCollection
            ->addGatewayFilter()
            ->addUnprocessedFilter()
            ->setPageSize(self::MAIL_LIMIT)
        ;
        foreach ($mailCollection as $mail) {
            //get exist or new ticket by mail subject
            $ticket = $this->getTicketByMailSubject($mail->getSubject());
            if ($ticket && !$this->followUpTicket) {
                $ticket->setStatus(\Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE);
            } else {
                $email = $this->parseEmail($mail->getFrom());
                $websites = $this->storeManager->getWebsites(true);
                $currentWebsite = null;
                $customer = null;
                $customerId = null;
                foreach ($websites as $id => $website) {
                    try {
                        $customer = $this->customerRepository->get($email, $id);
                        $currentWebsite = $id;
                        break;
                    } catch (\Exception $e) {
                        $customer = null;
                    }
                }
                $name = $this->parseCustomerName($mail->getFrom());

                if ($customer) {
                    $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerId = $customer->getId();
                }

                if ($currentWebsite) {
                    $storeId = $this->storeManager->getWebsite($currentWebsite)->getDefaultStore()->getId();
                } else {
                    $storeId = $mail->getStoreId();
                }
                $ticket = $this->ticketDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $ticket,
                    [],
                    '\Magento\Helpdesk\Api\Data\TicketInterface'
                );
                $subject = $mail->getSubject();
                if ($this->followUpTicket) {
                    $subject = $this->parseFollowUpSubject($subject);
                }

                /** @var DepartmentInterface $departmentDataObject */
                $departmentDataObject = $this->departmentRepository->getByGatewayEmail($mail->getGatewayEmail());

                $ticket
                    ->setAgentId(0)
                    ->setDepartmentId($departmentDataObject->getId())
                    ->setStatus(\Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE)
                    ->setPriority(\Aheadworks\Helpdesk\Model\Source\Ticket\Priority::DEFAULT_VALUE)
                    ->setCustomerId($customerId)
                    ->setCustomerName($name)
                    ->setCustomerEmail($email)
                    ->setSubject($subject)
                    ->setStoreId($storeId)
                ;

            }
            $ticket = $this->ticketRepository->save($ticket);
            $attachmentsCollection = $this->mailResource
                ->getAttachmentCollection()
                ->addFilterByMailId($mail->getId())
                ->getItems();

            $attachments = [];
            foreach ($attachmentsCollection as $attachment) {
                $attachments[] = $attachment->getData();
            }
            $threadMessage = $this->threadMessageFactory->create();
            $threadMessage
                ->setTicketId($ticket->getId())
                ->setContent($mail->getBody())
                ->setType(\Aheadworks\Helpdesk\Model\ThreadMessage::OWNER_CUSTOMER_VALUE)
                ->setAuthorName($ticket->getCustomerName())
                ->setAuthorEmail($ticket->getCustomerEmail())
                ->setCreatedAt($mail->getCreatedAt())
                ->setAttachment($attachments);
            $this->threadMessageResource->save($threadMessage);

            //update ticket flat
            try {
                $ticketFlat = $this->ticketFlatRepository->getByTicketId($ticket->getId());
            } catch (\Exception $e) {
                $ticketFlat = $this->ticketFlatDataFactory->create();
            }
            $ticketFlat->setData('order_id', $ticket->getOrderId());
            $ticketFlat->setData('agent_id', $ticket->getAgentId());
            $ticketFlat->setTicketId($ticket->getId());

            $this->ticketFlatRepository->save($ticketFlat);
            $mail->setStatus(\Aheadworks\Helpdesk\Model\Mail::STATUS_PROCESSED);
            $this->mailResource->save($mail);
        }
        $this->setLastExecTime(\Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_TICKET);
        return $this;
    }

    /**
     * Parse customer email from mail subject
     * @param string $str
     * @return bool|string
     */
    protected function parseEmail($str)
    {
        if (preg_match("/([a-z0-9.\-_]+@[a-z0-9.\-_]+)/i", $str, $matches)) {
            if (isset($matches[1])) {
                return strtolower($matches[1]);
            }
        }
        return false;
    }

    /**
     * Parse customer name from mail subject
     * @param string $str
     * @return string
     */
    protected function parseCustomerName($str)
    {
        $email = $this->parseEmail($str);
        return str_replace('<' . $email . '>', '', $str);
    }

    /**
     * Parse uid
     * @param $subject
     * @return null|string
     */
    protected function parseUid($subject) {
        $ticketUID = null;
        if (preg_match("/\[#([a-zA-Z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            if (isset($matches[1])) {
                $ticketUID = strtoupper($matches[1]);
            }
        }
        return $ticketUID;
    }

    protected function parseFollowUpSubject($subject) {
        if (preg_match("/\[#([a-zA-Z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            if (isset($matches[0])) {
                $subject = str_replace($matches[0], '', $subject);
            }
        }
        if ($subject == '') {
            $subject = __('No Subject');
        }
        return $subject;
    }

    /**
     * Get ticket by subject
     * @param $subject
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface|null
     */
    protected function getTicketByMailSubject($subject)
    {
        $ticketUID = $this->parseUid($subject);
        try {
            $ticket = $this->ticketRepository->getByUid($ticketUID);
            if ($ticket->getStatus() == \Aheadworks\Helpdesk\Model\Source\Ticket\Status::SOLVED_VALUE) {
                $this->followUpTicket = true;
            }
        } catch (\Exception $e) {
            $ticket = null;
            $this->followUpTicket = false;
        }
        return $ticket;
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
