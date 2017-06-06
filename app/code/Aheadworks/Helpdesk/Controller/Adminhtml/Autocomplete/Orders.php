<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Autocomplete;

use Aheadworks\Helpdesk\Model\TicketFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Orders
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Autocomplete
 */
class Orders extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
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
     * Order repository
     * @var OrderRepositoryInterface
     */
    private $orders;

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
        OrderRepositoryInterface $orders,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchBuilder
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->ticketFactory = $ticketFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orders = $orders;
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
        $customerEmail= $this->_request->getParam('customer_email', '');
        $storeId = $this->_request->getParam('store_id', '');

        $emailFilter = $this->filterBuilder
            ->setField('customer_email')
            ->setConditionType('eq')
            ->setValue($customerEmail)
            ->create();
        $storeFilter = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('eq')
            ->setValue($storeId)
            ->create();
        $criteria = $this->searchBuilder
            ->addFilters([$emailFilter])
            ->addFilters([$storeFilter])
            ->setCurrentPage($this->getRequest()->getParam('page', 1))
            ->setPageSize(30)
            ->create();
        $list = $this->orders->getList($criteria);
        $result = [];
        if ($list->getTotalCount()) {
            $unsignedLabel = __('Unassigned');
            $result['options'][] = "<option value='0'>{$unsignedLabel}</option>";
        }
        foreach ($list->getItems() as $item) {
            $result['options'][] = "<option value='{$item->getEntityId()}'>#{$item->getIncrementId()}</option>";
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}
