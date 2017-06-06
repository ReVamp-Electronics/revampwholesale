<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::departments';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DepartmentRepositoryInterface $departmentRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var DepartmentInterface $departmentDataObject */
                $departmentDataObject = $this->departmentRepository->getById($id);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This department no longer exists')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_Helpdesk::departments');
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Department') : __('New Department')
        );
        return $resultPage;
    }
}
