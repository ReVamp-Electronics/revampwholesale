<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Helper;

/**
 * Class Bookmark
 * @package Aheadworks\Helpdesk\Helper
 */
class Bookmark
{
    /**
     * Json decode interface
     *
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecode;

    /**
     * Json encode interface
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncode;

    /**
     * Bookmark factory
     *
     * @var \Magento\Ui\Api\Data\BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * Bookmark repository
     *
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * Constructor
     *
     * @param \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecode
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncode
     */
    public function __construct(
        \Magento\Ui\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecode,
        \Magento\Framework\Json\EncoderInterface $jsonEncode
    ) {
        $this->jsonDecode = $jsonDecode;
        $this->jsonEncode = $jsonEncode;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Create bookmark
     *
     * @param \Magento\User\Model\User $user
     */
    public function proceedAll(\Magento\User\Model\User $user)
    {
        $open = $this->getOpenTicketsBookmark($user);
        $new = $this->getNewTicketsBookmark($user);
        $pending = $this->getPendingTicketsBookmark($user);
        $solved = $this->getSolvedTicketsBookmark($user);
        $unassigned = $this->getUnassignedTicketsBookmark($user);

        $this->bookmarkRepository->save($open);
        $this->bookmarkRepository->save($new);
        $this->bookmarkRepository->save($pending);
        $this->bookmarkRepository->save($solved);
        $this->bookmarkRepository->save($unassigned);
    }

    /**
     * Create open tickets bookmark
     *
     * @param \Magento\User\Model\User $user
     * @return mixed
     */
    public function getOpenTicketsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->createNewTicketGridBookmark(
            $user, 'aw_helpdesk_ticket_listing', 'aw_helpdesk_ticket_open', __('Open Tickets')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'], [
                'order_increment_id'          => ['visible' => false, 'sorting' => false],
                'customer_messages'      => ['visible' => false, 'sorting' => false],
                'agent_messages'   => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'], [
                'status' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Create new ticket bookmark
     *
     * @param \Magento\User\Model\User $user
     * @return mixed
     */
    public function getNewTicketsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->createNewTicketGridBookmark(
            $user, 'aw_helpdesk_ticket_listing', 'aw_helpdesk_ticket_new', __('New Tickets')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'], [
                'order_increment_id'          => ['visible' => false, 'sorting' => false],
                'customer_messages'      => ['visible' => false, 'sorting' => false],
                'agent_messages'   => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'], [
                'status' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE,
                'agent_messages' => ['from' => '0', 'to' => '0']
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Create pending ticket bookmark
     *
     * @param \Magento\User\Model\User $user
     * @return mixed
     */
    public function getPendingTicketsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->createNewTicketGridBookmark(
            $user, 'aw_helpdesk_ticket_listing', 'aw_helpdesk_ticket_pending', __('Pending Tickets')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'], [
                'order_increment_id'          => ['visible' => false, 'sorting' => false],
                'customer_messages'      => ['visible' => false, 'sorting' => false],
                'agent_messages'   => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'], [
                'status' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::PENDING_VALUE
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Create solved ticket bookmark
     *
     * @param \Magento\User\Model\User $user
     * @return mixed
     */
    public function getSolvedTicketsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->createNewTicketGridBookmark(
            $user, 'aw_helpdesk_ticket_listing', 'aw_helpdesk_ticket_solved', __('Solved Tickets')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'], [
                'order_increment_id'          => ['visible' => false, 'sorting' => false],
                'customer_messages'      => ['visible' => false, 'sorting' => false],
                'agent_messages'   => ['visible' => false, 'sorting' => false],
            ]
        );
        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'], [
                'status' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::SOLVED_VALUE
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Create unassigned ticket bookmark
     *
     * @param \Magento\User\Model\User $user
     * @return mixed
     */
    public function getUnassignedTicketsBookmark(\Magento\User\Model\User $user)
    {
        $bookmark = $this->createNewTicketGridBookmark(
            $user, 'aw_helpdesk_ticket_listing', 'aw_helpdesk_ticket_unassigned', __('Unassigned Tickets')->render()
        );
        $config = $this->jsonDecode->decode($bookmark->getData('config'));
        $configData = &$config['views'][$bookmark->getIdentifier()];

        $configData['data']['columns'] = array_merge(
            $configData['data']['columns'], [
                'order_increment_id'          => ['visible' => false, 'sorting' => false],
                'customer_messages'      => ['visible' => false, 'sorting' => false],
                'agent_messages'   => ['visible' => false, 'sorting' => false],
            ]
        );

        $configData['data']['filters']['applied'] = array_merge(
            $configData['data']['filters']['applied'], [
                'status' => \Aheadworks\Helpdesk\Model\Source\Ticket\Status::OPEN_VALUE,
                'agent_id' => '0'
            ]
        );

        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Create grid bookmark
     *
     * @param \Magento\User\Model\User $user
     * @param $namespace
     * @param $identifier
     * @param $title
     * @return mixed
     */
    protected function createNewTicketGridBookmark(
        \Magento\User\Model\User $user, $namespace,
        $identifier, $title
    ) {
        return $this->createNewBookmark(
            $user, $namespace, $identifier, $title, $this->getTicketGridDefaultConfigData()
        );
    }

    /**
     * Create bookmark
     *
     * @param \Magento\User\Model\User $user
     * @param $namespace
     * @param $identifier
     * @param $title
     * @param $dataConfig
     * @return mixed
     */
    protected function createNewBookmark(
        \Magento\User\Model\User $user, $namespace,
        $identifier, $title, $dataConfig
    ) {
        $bookmark = $this->bookmarkFactory->create();
        $bookmark->addData([
            'user_id' => $user->getId(),
            'namespace' => $namespace,
            'identifier' => $identifier,
            'current' => '0',
            'title' => $title
        ]);
        $config = [
            'views' => [
                $identifier => [
                    'index' => $identifier,
                    'label' => $title,
                    'data' => $dataConfig
                ]
            ]
        ];
        $bookmark->setData('config', $this->jsonEncode->encode($config));
        return $bookmark;
    }

    /**
     * Get ticket grid default config
     *
     * @return array
     */
    public function getTicketGridDefaultConfigData()
    {
        return [
            "search"    => ['value' => ''],
            "filters"   => ["placeholder" => true, 'applied' => ["placeholder" => true]],
            "columns" => [
                "ids" => ["visible" => true, "sorting" => false],
                "uid" => ["visible" => true, "sorting" => false],
                "priority" => ["visible" => true, "sorting" => false],
                "status" => ["visible" => true, "sorting" => false],
                "subject" => ["visible" => true, "sorting" => false],
                "customer_name" => ["visible" => true, "sorting" => false],
                "last_reply_by" => ["visible" => true, "sorting" => false],
                "agent_name" => ["visible" => true, "sorting" => false],
                "last_reply_date" => ["visible" => true, "sorting" => 'asc'],
                "order_increment_id" => ["visible" => false, "sorting" => false],
                "customer_messages" => ["visible" => true, "sorting" => false],
                "agent_messages" => ["visible" => true, "sorting" => false],
                "store_id" => ["visible" => true, "sorting" => false]
            ],
            "paging"    => [
                "options" => [
                    "20"  => ["value" => 20, "label" => 20],
                    "30"  => ["value" => 30, "label" => 30],
                    "50"  => ["value" => 50, "label" => 50],
                    "100" => ["value" => 100, "label" => 100],
                    "200" => ["value" => 200, "label" => 200]
                ],
                "value"   => 20
            ],
            "displayMode" => "grid",
            "positions" => [
                "ids" => 0, "uid" => 10, "priority" => 20,
                "status" => 30, "subject" => 40, "customer_name" => 50,
                "last_reply_by" => 60, "agent_name" => 70, "last_reply_date" => 80,
                "order_increment_id" => 90, "customer_messages" => 100, "agent_messages" => 110,
                "store_id" => 120
            ]
        ];
    }
}