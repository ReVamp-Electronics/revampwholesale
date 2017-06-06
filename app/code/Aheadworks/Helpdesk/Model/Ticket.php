<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

use Magento\Customer\Model\ResourceModel\CustomerRepository;

/**
 * Class Ticket
 * @package Aheadworks\Helpdesk\Model
 */
class Ticket extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Data object processor
     *
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Ticket data factory
     *
     * @var \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory
     */
    protected $ticketDataFactory;

    /**
     * Customer Resource Model (by default)
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Thread collection
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $thread;

    const ABUSE_IDS = 'sex,wtf,fuc,fuk,fck,ass,hui,dck,pzd,ebl,bla,xep';

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Ticket $resource
     * @param ResourceModel\Ticket\Collection $resourceCollection
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param ResourceModel\ThreadMessage\Collection $threadMessageCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $resource = null,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Collection $resourceCollection = null,
        \Aheadworks\Helpdesk\Api\Data\TicketInterfaceFactory $ticketDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Aheadworks\Helpdesk\Model\ResourceModel\ThreadMessage\Collection $threadMessageCollection,
        array $data = []
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->ticketDataFactory = $ticketDataFactory;
        $this->customerRepository = $customerRepository;
        $this->thread = $threadMessageCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\Helpdesk\Model\ResourceModel\Ticket');
    }

    /**
     * Retrieve ticket model with ticket data
     *
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface
     */
    public function getDataModel()
    {
        $ticketData = $this->getData();
        $ticketDataObject = $this->ticketDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $ticketDataObject,
            $ticketData,
            '\Aheadworks\Helpdesk\Api\Data\TicketInterface'
        );

        if (!$this->getCustomerId()) {
            try {
                $customer = $this->customerRepository->get($this->getCustomerEmail(), $this->getWebsiteId());
                $ticketDataObject->setCustomerId($customer->getId());
            } catch (\Exception $e) {
                //Do nothing when "get" method returns exception
            }
        }
        $ticketDataObject->setId($this->getId());
        return $ticketDataObject;
    }

    /**
     * Update ticket data
     *
     * @param \Aheadworks\Helpdesk\Api\Data\TicketInterface $ticket
     * @return $this
     */
    public function updateData($ticket)
    {
        $ticketData = $this->dataObjectProcessor->buildOutputDataArray(
            $ticket,
            '\Aheadworks\Helpdesk\Api\Data\TicketInterface'
        );

        foreach ($ticketData as $key => $data) {
            $this->setDataUsingMethod($key, $data);
        }
        $ticketId = $ticket->getId();
        if ($ticketId) {
            $this->setId($ticketId);
        }
        return $this;
    }

    /**
     * Fill data
     *
     * @param $data
     * @throws \Magento\Framework\Validator\Exception
     */
    public function fillFromAdminData()
    {
        if ($this->getCcRecipients()) {
            $ccRecipients = $this->getCcRecipients();
            $this->setCcRecipients($this->prepareCC($ccRecipients));
        }

        if (!$this->getCustomerEmail() && !$this->getCustomerId()) {
            throw new \Magento\Framework\Validator\Exception(
                __('Email or Customer ID should be presented')
            );
        }

        try {
            if ($this->getCustomerId()) {
                $customer = $this->customerRepository->getById($this->getCustomerId());
            } else {
                $customer = $this->customerRepository->get($this->getCustomerEmail(), $this->getWebsiteId());
            }
        } catch (\Exception $e) {
            $customer = null;
        }

        if ($customer && (!$this->getCustomerId() || !$this->getCustomerName() || $this->getCustomerEmail())) {
            if (!$this->getCustomerName()) {
                $this->setCustomerName($customer->getFirstname() . ' ' . $customer->getLastname());
            }
            if (!$this->getCustomerId()) {
                $this->setCustomerId($customer->getId());
            }
            if (!$this->getCustomerEmail()) {
                $this->setCustomerEmail($customer->getEmail());
            }
        }

        if (!$this->getUid()) {
            $this->setUid($this->generateKey());
        }
    }


    /**
     * Prepare CC
     *
     * @param $string
     * @return array
     */
    protected function prepareCC($string)
    {
        $result = $string;
        if (is_string($string)) {
            $string = str_replace(' ', '', $string);
            $result = explode(',', $string);
        }
        return $result;
    }

    /**
     * Generate UID key
     * @return string
     */
    protected function generateKey()
    {
        /** @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket $resource */
        $resource = $this->_getResource();
        do {
            $digits = (string)mt_rand(100000, 199999);
            $digits = substr($digits, 1);

            $letters = '';
            $aZ = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $aZCount = strlen($aZ);
            for ($i = 0; $i < 3; $i++) {
                $int = mt_rand(0, $aZCount - 1);
                $letters .= $aZ[$int];
            }

            $result = $letters . '-' . $digits;
        } while (stripos(self::ABUSE_IDS, $letters) !== false || $resource->ifUidExist($result));

        return $result;
    }

    /**
     * Add cc_recepients validation to regular validation
     *
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validateBeforeSave()
    {
        try {
            parent::validateBeforeSave();
        } catch (\Magento\Framework\Validator\Exception $catchedException) {

        }

        if (!empty($catchedException)) {
            $validateException = $catchedException;
        } else {
            $validateException = new \Magento\Framework\Validator\Exception();
        }

        $this->fillFromAdminData();
        if ($this->getCcRecipients()) {
            $emailRule = new \Zend_Validate_EmailAddress();
            foreach ($this->getCcRecipients() as $item) {
                if (!$emailRule->isValid($item)) {
                    foreach ($emailRule->getMessages() as $message) {
                        $validateException->addMessage(new \Magento\Framework\Message\Error($message));
                    }
                }
            }
        }

        if (count($validateException->getMessages())) {
            throw $validateException;
        }

        return $this;
    }

    /**
     * Get validator before save
     * @return \Magento\Framework\Validator\DataObject|null|\Zend_Validate_Interface
     */
    protected function _getValidationRulesBeforeSave()
    {
        $validator = new \Magento\Framework\Validator\DataObject();
        return $validator;
    }

    /**
     * Get thread collection
     * @return ResourceModel\ThreadMessage\Collection
     */
    public function getThread()
    {
        $this->thread->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $this->thread
            ->getTicketThread($this->getId())
            ->setOrder('created_at')
            ->load()
        ;
        return $this->thread;
    }

    /**
     * Get frontend thread collection
     * @return ResourceModel\ThreadMessage\Collection
     */
    public function getFrontendThread()
    {
        $this->thread->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        $this->thread
            ->getTicketThread($this->getId())
            ->addNotInternalType()
            ->setOrder('created_at')
            ->load()
        ;
        return $this->thread;
    }
}