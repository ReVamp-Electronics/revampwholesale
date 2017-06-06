<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;

class Reply extends \Aheadworks\Rma\Controller\Adminhtml\Rma
{
    /**
     * @var \Aheadworks\Rma\Model\RequestManager
     */
    private $requestManager;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Aheadworks\Rma\Model\RequestManager $requestManager
    ) {
        $this->requestManager = $requestManager;
        $this->authSession = $authSession;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Reply action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data['owner_type'] = Owner::ADMIN_VALUE;
            $data['owner_id'] = $this->authSession->getUser()->getId();
            try {
                $this->requestManager->reply($data);
                $this->messageManager->addSuccess(__('Reply successfully added.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while adding the reply.'));
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $data['request_id']]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}