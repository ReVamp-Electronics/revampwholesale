<?php
namespace Evdpl\Jobopening\Controller\Adminhtml\index;

use Magento\Backend\App\Action;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update Jobopening post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $questionIds = $this->getRequest()->getParam('jobopening');
        if (!is_array($questionIds) || empty($questionIds)) {
            $this->messageManager->addError(__('Please select jobs(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                foreach ($questionIds as $postId) {
                    $post = $this->_objectManager->get('Evdpl\Jobopening\Model\Jobopening')->load($postId);
                    $post->setStatus($status)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($questionIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('jobopening/*/index');
    }

}
