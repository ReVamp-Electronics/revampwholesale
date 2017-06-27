<?php
namespace Evdpl\Ourteam\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Evdpl_Ourteam::post');
        $resultPage->addBreadcrumb(__('Our Team Member'), __('Our Team Member'));
        $resultPage->addBreadcrumb(__('Manage Our Team Members'), __('Manage Our Team Members'));
        $resultPage->getConfig()->getTitle()->prepend(__('Our Team Member'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the ourteam post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Evdpl_Ourteam::post');
    }


}
