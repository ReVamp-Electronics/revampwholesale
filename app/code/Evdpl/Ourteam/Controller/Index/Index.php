<?php
namespace Evdpl\Ourteam\Controller\Index;

use \Magento\Framework\App\Action\Action;

class Index extends Action
{
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Ourteam Index, shows a list of recent ourteam posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
         $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Our Team'));
        return $resultPage;
    }
}
