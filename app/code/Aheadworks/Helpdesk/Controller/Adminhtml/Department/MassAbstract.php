<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory as DepartmentCollectionFactory;

/**
 * Class MassAbstract
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
abstract class MassAbstract extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::departments';

    /**
     * @var DepartmentRepositoryInterface
     */
    protected $departmentRepository;

    /**
     * @var DepartmentCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while perform mass action';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DepartmentCollectionFactory $collectionFactory
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        DepartmentCollectionFactory $collectionFactory,
        DepartmentRepositoryInterface $departmentRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($this->errorMessage);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Performs mass action
     *
     * @param DepartmentCollection $collection
     * @return void
     */
    abstract protected function massAction($collection);
}
