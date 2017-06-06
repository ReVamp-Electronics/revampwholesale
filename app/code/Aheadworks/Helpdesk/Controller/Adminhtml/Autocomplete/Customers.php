<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Autocomplete;

use Aheadworks\Helpdesk\Model\TicketFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Customers
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Autocomplete
 */
class Customers extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Ticket factory
     * @var TicketFactory
     */
    protected $ticketFactory;

    /**
     * Json factory
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Customer repository
     * @var CustomerRepositoryInterface
     */
    private $customers;

    /**
     * Search criteria builder
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * Filter builder
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param TicketFactory $ticketFactory
     * @param CustomerRepositoryInterface $customers
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        TicketFactory $ticketFactory,
        CustomerRepositoryInterface $customers,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchBuilder
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->ticketFactory = $ticketFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customers = $customers;
        $this->searchBuilder = $searchBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $query = $this->_request->getParam('query', '');
        $result = array(
            'query'  => $query,
            'suggestions' => [],
        );
        $filter = $this->filterBuilder
            ->setField('email')
            ->setConditionType('like')
            ->setValue('%' . $query . '%')
            ->create();
        $criteria = $this->searchBuilder
            ->addFilter($filter)
            ->setCurrentPage($this->getRequest()->getParam('page', 1))
            ->setPageSize(30)
            ->create();
        $list = $this->customers->getList($criteria);
        foreach ($list->getItems() as $item) {
            $result['suggestions'][] = [
                'value' => $item->getEmail(),
                'customer_name' => $item->getFirstname() . ' ' . $item->getLastname()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}