<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit;

/**
 * Class Tabs
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Block template path
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * Ticket repository model (by default)
     *
     * @var \Aheadworks\Helpdesk\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Customer repository model (by default)
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Order repository model (by default)
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ticketRepository = $ticketRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Initialize Tabs
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ticket_edit_tabs');
        $this->setDestElementId('edit_form');
        $ticketModel = $this->coreRegistry->registry("aw_helpdesk_ticket");
        $this->setTitle(htmlspecialchars_decode(__('Ticket [%s]', $ticketModel->getUid()) . " " . addslashes($ticketModel->getSubject())));
    }

    /**
     * Prepare Layout Content
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\General',
                    'aw_helpdesk_edit_tabs_general'
                )->toHtml()
            ]
        );

        $ticketModel = $this->coreRegistry->registry("aw_helpdesk_ticket");
        try {
            if ($ticketModel->getCustomerId()) {
                $customer = $this->customerRepository->getById($ticketModel->getCustomerId());
            } else {
                $customer = $this->customerRepository->get($ticketModel->getCustomerEmail(), $ticketModel->getWebsiteId());
            }
        } catch (\Exception $e) {
            $customer = null;
        }


        if ($customer) {
            $name = $customer->getFirstname() . " " . $customer->getLastname();
        } else {
            $name = $ticketModel->getCustomerName();
        }

        $this->addTab(
            'customer',
            [
                'label' => $name,
                'content' => $this->getLayout()->createBlock(
                        'Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Customer',
                        'aw_helpdesk_edit_tabs_customer'
                    )->toHtml()
            ]
        );

        $ticketList = $this->getTicketList();
        if (count($ticketList) > 0) {
            $this->addTab(
                'tickets',
                [
                    'label' => __('Tickets'),
                    'content' => $this->getLayout()->createBlock(
                            'Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Ticket',
                            'aw_helpdesk_edit_tabs_tickets'
                        )->toHtml()
                ]
            );
        }

        $orderList = $this->getOrderList();
        if (count($orderList) > 0) {
            $this->addTab(
                'purchases',
                [
                    'label' => __('Purchases'),
                    'content' => $this->getLayout()->createBlock(
                            'Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Purchases',
                            'aw_helpdesk_edit_tabs_purchases'
                        )->toHtml()
                ]
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * Get tickets list
     *
     * @return \Aheadworks\Helpdesk\Api\Data\TicketInterface[]
     */
    private function getTicketList()
    {
        $ticketModel = $this->coreRegistry->registry("aw_helpdesk_ticket");
        $customerDataFilter = [];
        $notCurrentIdFilter[] = $this->filterBuilder
            ->setField(\Aheadworks\Helpdesk\Api\Data\TicketInterface::ID)
            ->setConditionType('neq')
            ->setValue($ticketModel->getId())
            ->create();
        $customerEmailTicket = $this->filterBuilder
            ->setField(\Aheadworks\Helpdesk\Api\Data\TicketInterface::CUSTOMER_EMAIL)
            ->setConditionType('eq')
            ->setValue($ticketModel->getCustomerEmail())
            ->create();
        $customerDataFilter[] = $customerEmailTicket;
        if ($ticketModel->getCustomerId()) {
            $customerIdTicket = $this->filterBuilder
                ->setField(\Aheadworks\Helpdesk\Api\Data\TicketInterface::CUSTOMER_ID)
                ->setConditionType('eq')
                ->setValue($ticketModel->getCustomerId())
                ->create();
            $customerDataFilter[] = $customerIdTicket;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($notCurrentIdFilter)
            ->addFilters($customerDataFilter)
            ->create();
        return $this->ticketRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get orders list
     *
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    private function getOrderList()
    {
        $ticketModel = $this->coreRegistry->registry("aw_helpdesk_ticket");
        $customerDataFilter = [];
        $notCurrentIdFilter = [];
        if ($ticketModel->getOrderId()) {
            $notCurrentIdFilter[] = $this->filterBuilder
                ->setField(\Magento\Sales\Api\Data\OrderInterface::ENTITY_ID)
                ->setConditionType('neq')
                ->setValue($ticketModel->getOrderId())
                ->create();
        }

        $customerEmailOrder = $this->filterBuilder
            ->setField(\Magento\Sales\Api\Data\OrderInterface::CUSTOMER_EMAIL)
            ->setConditionType('eq')
            ->setValue($ticketModel->getCustomerEmail())
            ->create();
        $customerDataFilter[] = $customerEmailOrder;
        if ($ticketModel->getCustomerId()) {
            $customerIdOrder = $this->filterBuilder
                ->setField(\Magento\Sales\Api\Data\OrderInterface::CUSTOMER_ID)
                ->setConditionType('eq')
                ->setValue($ticketModel->getCustomerId())
                ->create();
            $customerDataFilter[] = $customerIdOrder;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($notCurrentIdFilter)
            ->addFilters($customerDataFilter)
            ->create();

        return $this->orderRepository->getList($searchCriteria)->getItems();
    }
}