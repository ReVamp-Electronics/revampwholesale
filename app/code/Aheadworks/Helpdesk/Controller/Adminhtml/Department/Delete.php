<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Helpdesk\Api\TicketRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Helpdesk\Api\Data\TicketInterface;
use Aheadworks\Helpdesk\Api\Data\TicketSearchResultsInterface;

/**
 * Class Delete
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::departments';

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

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
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param TicketRepositoryInterface $ticketRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        DepartmentRepositoryInterface $departmentRepository,
        TicketRepositoryInterface $ticketRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->departmentRepository = $departmentRepository;
        $this->ticketRepository = $ticketRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                /** @var \Aheadworks\Helpdesk\Api\Data\DepartmentInterface $departmentDataObject */
                $departmentDataObject = $this->departmentRepository->getById($id);
                if ($departmentDataObject->getIsDefault()) {
                    throw new LocalizedException(__('Default department can not be deleted'));
                }
                $this->searchCriteriaBuilder
                    ->addFilter(TicketInterface::DEPARTMENT_ID, $departmentDataObject->getId());

                /** @var TicketSearchResultsInterface $result */
                $result = $this->ticketRepository->getList($this->searchCriteriaBuilder->create());
                if ($result->getTotalCount() > 0) {
                    throw new LocalizedException(
                        __(
                            'You can delete department only if there are no tickets assigned to it.'
                            . ' Please assign such tickets to other department first.'
                        )
                    );
                }

                $this->departmentRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Department was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }
        $this->messageManager->addErrorMessage(__('Department can\'t be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
