<?php
namespace Evdpl\Jobopening\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $jobIds = $this->getRequest()->getParam('jobopening');
        if (!is_array($jobIds) || empty($jobIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($jobIds as $postId) {
                    $post = $this->_objectManager->get('Evdpl\Jobopening\Model\Jobopening')->load($postId);
                    $post->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($jobIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('jobopening/*/index');
    }
}
?>