<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Evdpl\Faques\Controller\Index;

class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * Show Contact Us page
     *
     * @return void
     */
    protected $_objectManager;
    
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\ObjectManagerInterface $objectManager) 
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context);    
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $currenttime = date('Y-m-d H:i:s');
        $model = $this->_objectManager->create('Evdpl\Faques\Model\Question');
        $model->setData('faq_question', $post['faq_question']);
        $model->setData('question', $post['question']);
        $model->setData('status', 2);
        $model->setData('created_at', $currenttime);
        $model->setData('publish_date', $currenttime);
        $model->save();
        $this->_redirect('*/*/');
        $this->messageManager->addSuccess(__('Your question has beeen submitted successfully.'));    
    }
}
