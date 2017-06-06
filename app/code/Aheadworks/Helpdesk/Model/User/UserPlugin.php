<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\User;

/**
 * Class UserPlugin
 * @package Aheadworks\Helpdesk\Model\User
 */
class UserPlugin
{
    /**
     * Bookmark helper
     *
     * @var \Aheadworks\Helpdesk\Helper\Bookmark
     */
    protected $definedBookmarkHelper;

    /**
     * Bookmark repository
     *
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * Ticket collection factory
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory
     */
    protected $ticketCollection;

    /**
     * Ticket repository
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * Ticket flat repository
     * @var \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface
     */
    protected $ticketFlatRepository;

    /**
     * Constructor
     *
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Aheadworks\Helpdesk\Helper\Bookmark $bookmarkHelper
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollection
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Aheadworks\Helpdesk\Helper\Bookmark $bookmarkHelper,
        \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory $ticketCollection,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Aheadworks\Helpdesk\Api\TicketFlatRepositoryInterface $ticketFlatRepository
    ) {
        $this->definedBookmarkHelper = $bookmarkHelper;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->ticketCollection = $ticketCollection;
        $this->ticketRepository = $ticketRepository;
        $this->ticketFlatRepository = $ticketFlatRepository;
    }

    /**
     * Run plugin after save user
     *
     * @param \Magento\User\Model\User $subject
     * @param callable $proceed
     */
    public function aroundAfterSave(
        \Magento\User\Model\User $subject,
        \Closure $proceed
    ) {
        $proceed();
        if ($subject->isObjectNew()) {
            $this->definedBookmarkHelper->proceedAll($subject);
        }
    }

    /**
     * Run plugin after delete user
     *
     * @param \Magento\User\Model\User $subject
     * @param $result
     * @return mixed
     */
    public function afterDelete(
        \Magento\User\Model\User $subject,
        $result
    ) {
        $userId = $subject->getUserId();

        $ticketCollection = $this->ticketCollection->create()->addFilter('agent_id', $userId, 'public')->load();
        foreach ($ticketCollection as $ticket) {
            try {
                $ticketModel = $this->ticketRepository->getById($ticket->getId());
                $ticketModel->setAgentId(0);
                $this->ticketRepository->save($ticketModel);
                $ticketFlatModel = $this->ticketFlatRepository->getByTicketId($ticket->getId());
                $ticketFlatModel->setAgentId(0);
                $ticketFlatModel->setAgentName(__('Unassigned'));
                $this->ticketFlatRepository->save($ticketFlatModel);
            } catch (\Exception $e) {
                continue;
            }
        }
        return $result;
    }
}