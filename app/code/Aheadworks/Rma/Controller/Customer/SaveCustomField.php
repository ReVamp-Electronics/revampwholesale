<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class SaveCustomField
 * @package Aheadworks\Rma\Controller\Customer
 */
class SaveCustomField extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    private $customFieldFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customFieldFactory = $customFieldFactory;
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
     * @return $this
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            $rmaRequestId = $data['request_id'];
            $customFieldId = array_keys($data['custom_fields'])[0];
            $customField = $this->customFieldFactory->create()
                ->setStoreId($this->storeManager->getStore()->getId())
                ->load($customFieldId)
            ;
            try {
                $rmaRequest = $this->loadRmaRequest($rmaRequestId);
                if ($this->isRequestValid($rmaRequest)) {
                    $this->requestManager->updateCustomFieldValue($rmaRequest, $data['custom_fields']);
                    $this->messageManager->addSuccessMessage(
                        __('%1 has been successfully saved.', $customField->getAttribute('frontend_label'))
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the %1.', $customField->getAttribute('frontend_label'))
                );
            }
            return $resultRedirect->setPath('*/*/view', ['id' => $rmaRequest->getId()]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}