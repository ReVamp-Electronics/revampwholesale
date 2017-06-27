<?php
namespace Evdpl\Jobopening\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	
    /**
     * @param Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry)
    {
		$this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Evdpl_Jobopening::save');
    }

    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Evdpl_Jobopening::jobopening_manage'
        )->addBreadcrumb(
            __('Jobopening'),
            __('Jobopening')
        )->addBreadcrumb(
            __('Manage Jobopening'),
            __('Manage Jobopening')
        );
		return $resultPage;
    }

   
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager->create('Evdpl\Jobopening\Model\Jobopening');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This jobopening no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
       
        if (!empty($data)) {
            $model->setData($data);
        }

        
        $this->_coreRegistry->register('jobopening', $model);

        
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit jobopening') : __('New jobopening'),
            $id ? __('Edit jobopening') : __('New jobopening')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Jobopening'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Jobopening'));
			
        return $resultPage;
    }
}
