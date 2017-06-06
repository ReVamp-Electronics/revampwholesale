<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Ticket;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Aheadworks\Helpdesk\Api\Data\TicketInterface;

/**
 * Class Items
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Ticket
 */
class Items implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Block template path
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::ticket/edit/tabs/ticket.phtml';

    /**
     * Template Block
     *
     * @var \Magento\Backend\Block\Template
     */
    protected $block;

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
     * Constructor
     *
     * @param \Magento\Backend\Block\Template $block
     * @param \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Backend\Block\Template $block,
        \Aheadworks\Helpdesk\Api\TicketRepositoryInterface $ticketRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->block = $block;
        $this->ticketRepository = $ticketRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Render element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $customerDataFilter = [];
        $notCurrentIdFilter[] = $this->filterBuilder
            ->setField(TicketInterface::ID)
            ->setConditionType('neq')
            ->setValue($element->getTicketId())
            ->create();
        $customerEmailTicket = $this->filterBuilder
            ->setField(TicketInterface::CUSTOMER_EMAIL)
            ->setConditionType('eq')
            ->setValue($element->getCustomerEmail())
            ->create();
        $customerDataFilter[] = $customerEmailTicket;
        if ($element->getCustomerId()) {
            $customerIdTicket = $this->filterBuilder
                ->setField(TicketInterface::CUSTOMER_ID)
                ->setConditionType('eq')
                ->setValue($element->getCustomerId())
                ->create();
            $customerDataFilter[] = $customerIdTicket;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($notCurrentIdFilter)
            ->addFilters($customerDataFilter)
            ->create();
        $ticketCollection = $this->ticketRepository->getList($searchCriteria)->getItems();

        $html = "<div class='admin__field field field-ticket_items'>";
        $html .=  $this->block
            ->setTicketCollection($ticketCollection)
            ->setTemplate($this->_template)
            ->toHtml();
        $html .= "</div>";
        return $html;
    }
}
