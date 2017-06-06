<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CreateRequestStep
 * @package Aheadworks\Rma\Controller\Customer
 */
class CreateRequestStep extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Rma\Model\RequestManager $requestManager,
        \Aheadworks\Rma\Model\RequestFactory $requestFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $formKeyValidator,
            $scopeConfig,
            $requestManager,
            $requestFactory,
            $customerSession
        );
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $order = $this->orderFactory->create();
        try {
            if ($this->getRequest()->getMethod() == \Magento\Framework\App\Request\Http::METHOD_POST) {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/');
                }
                $data = $this->getRequest()->getPostValue();
                $this->customerSession->setOrderSelectData($data);
                if ($data) {
                    $orderId = isset($data['order_id']) ? $data['order_id'] : null;
                }
            } else {
                if ($data = $this->customerSession->getOrderSelectData()) {
                    $orderId = $data['order_id'];
                } else {
                    $orderId = $this->getRequest()->getParam('id');
                    $data = ['order_id' => $orderId];
                }
            }

            if (!$orderId) {
                throw new LocalizedException(__('Order is not specified.'));
            }
            $order->load($orderId);

            $this->coreRegistry->register('aw_rma_request_data', $data);
            if ($formData = $this->customerSession->getFormData()) {
                $this->coreRegistry->register('aw_rma_form_data', $formData);
            }

            /** $resultPage @var \Magento\Framework\View\Result\Page */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('New Return for Order #%1', $order->getIncrementId()));
            /** @var \Magento\Customer\Block\Account\Dashboard $linkBack */
            $linkBack = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($linkBack) {
                $linkBack->setRefererUrl($this->_redirect->getRefererUrl());
            }
            return $resultPage;

        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while loadin the page.'));
        }
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
    }
}
