<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class CreateRequest
 * @package Aheadworks\Rma\Controller\Guest
 */
class CreateRequest extends \Aheadworks\Rma\Controller\Guest
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    private $orderHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Rma\Model\RequestManager $requestManager,
        \Aheadworks\Rma\Model\RequestFactory $requestFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Aheadworks\Rma\Helper\Order $orderHelper
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $formKeyValidator,
            $scopeConfig,
            $requestManager,
            $requestFactory
        );
        $this->orderFactory = $orderFactory;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                if (!isset($data['order_increment_id'])) {
                    throw new LocalizedException(__('Order Number isn\'t specified.'));
                }
                if (!isset($data['email'])) {
                    throw new LocalizedException(__('Email isn\'t specified.'));
                }
                $orderIncrementId = trim($data['order_increment_id']);
                $orderIncrementId = preg_replace('/^#/', '', $orderIncrementId);
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()
                    ->loadByIncrementId($orderIncrementId)
                ;
                if (!$order->getId()) {
                    throw new LocalizedException(__('Couldn\'t load order by given Order Number'));
                }
                if (strcasecmp($order->getCustomerEmail(), $data['email'])) {
                    throw new LocalizedException(__('Order Number and Email didn\'t match each other'));
                }
                if ($order->getCustomerId()) {
                    throw new LocalizedException(__('This order has been placed by registered customer. Please, authorize and request RMA via customer account.'));
                }
                if (!$this->orderHelper->isAllowedForOrder($order)) {
                    throw new LocalizedException(__(
                        'Specified order has been created more than %1 days ago or has not been completed',
                        $this->scopeConfig->getValue(
                            'aw_rma/general/return_period',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        )
                    ));
                }

                $data['order_id'] = $order->getId();
                $this->coreRegistry->register('aw_rma_request_data', $data);

                return $this->getResultPage([
                    'title' => __('New Return for Order #%1', $orderIncrementId),
                    'link_back' => ['name' => 'guest.link.back', 'route_path' => 'aw_rma/guest']
                ]);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while creating the return.'));
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}