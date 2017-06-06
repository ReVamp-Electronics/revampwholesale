<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Helpdesk\Api\Data\TicketInterface;
use Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface;

/**
 * Class MassDelete
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class MassDelete extends \Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while deleting department(s)';

    /**
     * @var TicketRepositoryInterface
     */
    private $ticketRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DepartmentCollectionFactory $collectionFactory
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param TicketRepositoryInterface $ticketRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        DepartmentCollectionFactory $collectionFactory,
        DepartmentRepositoryInterface $departmentRepository,
        TicketRepositoryInterface $ticketRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($context, $filter, $collectionFactory, $departmentRepository);
    }

    /**
     * {@inheritdoc}
     */
    protected function massAction($collection)
    {
        $count = 0;
        /** @var \Aheadworks\Helpdesk\Model\Department $department */
        foreach ($collection->getItems() as $department) {
            /** @var \Aheadworks\Helpdesk\Api\Data\DepartmentInterface $departmentDataObject */
            $departmentDataObject = $this->departmentRepository->getById($department->getId());
            if ($departmentDataObject->getIsDefault()) {
                $this->messageManager->addErrorMessage(
                    __('Default department %1 can not be deleted', $departmentDataObject->getName())
                );
                continue;
            }
            $this->searchCriteriaBuilder
                ->addFilter(TicketInterface::DEPARTMENT_ID, $departmentDataObject->getId());

            /** @var TicketSearchResultsInterface $result */
            $result = $this->ticketRepository->getList($this->searchCriteriaBuilder->create());
            if ($result->getTotalCount() > 0) {
                $this->messageManager->addErrorMessage(
                    __(
                        'You can delete department only if there are no tickets assigned to it.'
                        . ' Please assign such tickets to other department first.'
                    )
                );
                continue;
            }

            if ($this->departmentRepository->deleteById($departmentDataObject->getId())) {
                $count++;
            }
        }
        if ($count > 0) {
            $this->messageManager->addSuccessMessage(__('A total of %1 department(s) have been deleted', $count));
        }
    }
}
