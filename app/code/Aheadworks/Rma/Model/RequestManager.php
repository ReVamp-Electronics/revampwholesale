<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Model\Source\Request\Status as StatusSource;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Setup\Exception;
use Magento\Store\Model\ScopeInterface as StoreScope;

/**
 * Class RequestManager
 * @package Aheadworks\Rma\Model
 */
class RequestManager
{
    const XML_PATH_ALLOW_GUEST_REQUESTS = 'aw_rma/general/allow_guest_requests';
    const XML_PATH_DEPARTMENT_NAME = 'aw_rma/contacts/department_name';
    const XML_PATH_DEPARTMENT_EMAIL = 'aw_rma/contacts/department_email';

    /**
     * Location of the "Reply by Admin" config param
     */
    const XML_PATH_TEMPLATE_TO_CUSTOMER = 'aw_rma/email/template_to_customer_thread';

    /**
     * Location of the "Reply by Customer" config param
     */
    const XML_PATH_TEMPLATE_TO_ADMIN = 'aw_rma/email/template_to_admin_thread';

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var ThreadMessageFactory
     */
    protected $threadMessageFactory;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Aheadworks\Rma\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Aheadworks\Rma\Helper\Status
     */
    protected $statusHelper;

    /**
     * @var PrintLabel\Pdf
     */
    protected $printLabelPdf;

    /**
     * @param RequestFactory $requestFactory
     * @param StatusFactory $statusFactory
     * @param ThreadMessageFactory $threadMessageFactory
     * @param Sender $sender
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Rma\Helper\Order $orderHelper
     * @param \Aheadworks\Rma\Helper\Status $statusHelper
     * @param PrintLabel\Pdf $printLabelPdf
     */
    public function __construct(
        RequestFactory $requestFactory,
        StatusFactory $statusFactory,
        ThreadMessageFactory $threadMessageFactory,
        Sender $sender,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Rma\Helper\Order $orderHelper,
        \Aheadworks\Rma\Helper\Status $statusHelper,
        \Aheadworks\Rma\Model\PrintLabel\Pdf $printLabelPdf
    ) {
        $this->requestFactory = $requestFactory;
        $this->statusFactory = $statusFactory;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->sender = $sender;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->customerSession = $customerSession;
        $this->orderHelper = $orderHelper;
        $this->statusHelper = $statusHelper;
        $this->printLabelPdf = $printLabelPdf;
    }

    /**
     * Create new RMA request
     *
     * @param array $data
     * @param bool $guestMode
     * @return Request
     * @throws LocalizedException
     */
    public function create($data, $guestMode = false)
    {
        /** @var Request $requestModel */
        $requestModel = $this->requestFactory->create()
            ->setData($this->prepareAndValidate($data, $guestMode))
            ->setStatusId(StatusSource::PENDING_APPROVAL)
            ->save()
        ;

        $replyData = [
            'request_id' => $requestModel->getId(),
            'text' => $data['text'],
            'owner_type' => Owner::CUSTOMER_VALUE
        ];
        if (isset($data['attachment'])) {
            $replyData['attachment'] = $data['attachment'];
        }
        if (!$guestMode) {
            $replyData['owner_id'] = $requestModel->getCustomerId();
        }
        $this->reply($replyData);
        $this->notifyAboutStatusChange($requestModel);
        return $requestModel;
    }

    private function prepareAndValidate($data, $guestMode)
    {
        if (!isset($data['order_id'])) {
            throw new LocalizedException(__('Wrong form data.'));
        }
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create()->load($data['order_id']);
        $data['store_id'] = $order->getStoreId();
        $data['payment_method'] = $order->getPayment()->getMethod();
        if (!$order->getId()) {
            throw new LocalizedException(__('Wrong order ID.'));
        }
        if (!$this->orderHelper->isAllowedForOrder($order)) {
            throw new LocalizedException(__('You can\'t request RMA for the given order.'));
        }
        if ($guestMode) {
            if (strcasecmp($order->getCustomerEmail(), $data['customer_email'])) {
                throw new LocalizedException(__('You are not owner of the given order.'));
            }
        } else {
            if ($order->getCustomerId() != $this->customerSession->getCustomerId()) {
                throw new LocalizedException(__('Customer isn\'t owner of the given order.'));
            }
        }
        if (!isset($data['items'])) {
            throw new LocalizedException(__('No item(s) for request specified.'));
        }

        $data['items'] = $this->prepareRequestItems($data['items']);

        $isVirtual = true;
        foreach ($order->getItemsCollection() as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            if (array_key_exists($orderItem->getId(), $data['items'])) {
                if (isset($data['items'][$orderItem->getId()]['qty'])) {
                    $itemQty = $data['items'][$orderItem->getId()]['qty'];
                    if ($itemQty < 0 || $itemQty > $this->orderHelper->getItemMaxCount($orderItem)) {
                        throw new LocalizedException(__('Wrong quantity for %1.', $orderItem->getName()));
                    }
                } else {
                    throw new LocalizedException(__('Wrong form data.'));
                }
            }
            if (!$orderItem->getIsVirtual()) {
                $isVirtual = false;
            }
        }

        if ($guestMode) {
            $billingAddress = $order->getBillingAddress();
            $data['customer_name'] = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
            $data['customer_email'] = $order->getCustomerEmail();
        } else {
            $customerData = $this->customerSession->getCustomerData();
            $data['customer_name'] = $customerData->getFirstname() . ' ' . $customerData->getLastname();
            $data['customer_email'] = $customerData->getEmail();
            $data['customer_id'] = $this->customerSession->getCustomerId();
        }

        $data['print_label'] = $this->getPrintLabel(
            $isVirtual ? $order->getBillingAddress() : $order->getShippingAddress()
        );
        $data['external_link'] = $this->generateExternalLink();
        return $data;
    }

    /**
     * Prepare request items data
     *
     * @param array $dataItemsRaw
     * @return array
     */
    private function prepareRequestItems($dataItemsRaw)
    {
        $dataItemsPrepared = [];
        while ($itemData = array_shift($dataItemsRaw)) {
            $empty = true;
            $foundSame = false;
            foreach ($dataItemsPrepared as &$itemPrepared) {
                $empty = false;
                $equals = true;
                if ($itemPrepared['item_id'] == $itemData['item_id']) {
                    $foundSame = true;
                    foreach ($itemPrepared['custom_fields'] as $customFieldId => $customFieldValue) {
                        $rawValue = $itemData['custom_fields'][$customFieldId];
                        if (
                            isset($itemPrepared['custom_fields']) &&
                            (
                                (is_numeric($customFieldValue) && (int)$customFieldValue === (int)$rawValue) ||
                                (is_string($customFieldValue) && trim($customFieldValue) === trim($rawValue)) ||
                                (is_array($customFieldValue) && !count(array_diff($customFieldValue, $rawValue)))
                            )
                        ) {
                            continue;
                        }
                        $equals = false;
                        break;
                    }
                    if ($equals) {
                        $itemPrepared['qty']++;
                    } else {
                        $dataItemsPrepared[] = $itemData;
                    }
                    break;
                }
            }
            if ($empty || !$foundSame) {
                $dataItemsPrepared[] = $itemData;
            }
        };
        return $dataItemsPrepared;
    }

    /**
     * Retrieves print label data for request
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    private function getPrintLabel(\Magento\Sales\Model\Order\Address $address)
    {
        return [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'company' => $address->getCompany(),
            'fax' => $address->getFax(),
            'street' => implode('\n', $address->getStreet()),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'region_id' => $address->getRegionId(),
            'country_id' => $address->getCountryId(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
        ];
    }


    /**
     * Generate external link for request
     *
     * @return string
     */
    private function generateExternalLink()
    {
        return strtoupper(uniqid(dechex(rand())));
    }

    /**
     * @param int|string|Request $request
     * @param int $statusId
     * @param bool $admin
     * @return Request
     * @throws LocalizedException
     */
    public function setStatus($request, $statusId, $admin = false)
    {
        /** @var Request $requestModel */
        $requestModel = $this->getRequestModel($request);
        if ($this->statusHelper->isAvailableForCustomer($statusId, $requestModel->getStatusId())) {
            $requestModel
                ->setStatusId($statusId)
                ->save()
            ;
        } else {
            throw new LocalizedException('Unable to set status');
        }
        $this->notifyAboutStatusChange($requestModel);
        return $requestModel;
    }

    /**
     * @param int|string|Request $request
     * @param $data
     */
    public function updatePrintLabel($request, $data)
    {
        /** @var Request $requestModel */
        $requestModel = $this->getRequestModel($request);
        $data['street'] = implode('\n', $data['street']);
        $requestPrintLabel = $requestModel->getPrintLabel();
        foreach ($data as $key => $value) {
            $requestPrintLabel[$key] = $value;
        }
        $requestModel
            ->setPrintLabel($requestPrintLabel)
            ->save()
        ;
    }

    /**
     * @param int|string|Request $request
     * @return string
     */
    public function generatePrintLabelPdf($request)
    {
        return $this->printLabelPdf->getPdf($this->getRequestModel($request));
    }

    /**
     * @param int|string|Request $request
     * @param $data
     */
    public function updateCustomFieldValue($request, $data)
    {
        /** @var Request $requestModel */
        $requestModel = $this->getRequestModel($request);
        $requestCustomFields = $requestModel->getCustomFields();
        foreach ($data as $customFieldId => $customFieldValue) {
            $requestCustomFields[$customFieldId] = $customFieldValue;
        }
        $requestModel
            ->setCustomFields($requestCustomFields)
            ->save()
        ;
    }

    /**
     * @param $data
     * @throws Exception
     * @throws LocalizedException
     */
    public function reply($data)
    {
        if (!isset($data['request_id']) || !isset($data['owner_type'])) {
            throw new Exception("Invalid message data supplied.");
        }
        $requestModel = $this->getRequestModel($data['request_id']);
        $data['request_id'] = $requestModel->getId();
        $this->addToThread($data);
        $requestModel
            ->setLastReplyBy($data['owner_type'])
            ->save()
        ;
        $this->notifyAboutNewMessage($requestModel, $data['text'], $data['owner_type']);
    }

    /**
     * @param int|string|Request $request
     * @return Request|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequestModel($request)
    {
        $requestModel = null;
        if (is_numeric($request)) {
            $requestModel = $this->requestFactory->create()->load($request);
        } elseif (is_string($request)) {
            $requestModel = $this->requestFactory->create()->loadByExternalLink($request);
        } elseif ($request instanceof Request) {
            $requestModel = $request;
        }
        if (!$requestModel || !$requestModel->getId()) {
            throw new LocalizedException(__('Request doesn\'t exists'));
        }
        return $requestModel;
    }

    /**
     * @param string|Request $request
     * @return Request|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequestModelForGuest($request)
    {
        $requestModel = null;
        if (is_string($request)) {
            $requestModel = $this->requestFactory->create()->loadByExternalLink($request);
        } elseif ($request instanceof Request) {
            $requestModel = $request;
        }
        if (!$requestModel || !$requestModel->getId()) {
            throw new LocalizedException(__('Request doesn\'t exists'));
        }
        return $requestModel;
    }

    /**
     * @param int|string|Request $request
     */
    public function notifyAboutStatusChange($request)
    {
        $requestModel = $this->getRequestModel($request);
        $statusId = $requestModel->getStatusId();
        /** @var Status $statusModel */
        $statusModel = $this->statusFactory->create()
            ->setStoreId($requestModel->getStoreId())
            ->load($statusId)
        ;
        $request->setStatusFrontendLabel($statusModel->getAttribute('frontend_label'));
        if ($statusModel->getIsEmailCustomer()) {
            $this->send($requestModel, $statusModel->getAttribute('template_to_customer'), false);
        }
        $isCancelByAdmin = $this->coreRegistry->registry('aw_rma_cancel_by_admin');
        if (
            $statusModel->getIsEmailAdmin()
            && !($statusId == StatusSource::CANCELED && $isCancelByAdmin)
        ) {
            $this->send($requestModel, $statusModel->getAttribute('template_to_admin'));
        }
        if ($statusModel->getIsThread()) {
            $this->addToThread(
                [
                    'text' => $statusModel->getAttribute('template_to_thread'),
                    'owner_type' => Owner::ADMIN_VALUE,
                    'is_auto' => true,
                    'request_id' => $requestModel->getId()
                ]
            );
        }
    }

    /**
     * Send email about new message
     *
     * @param Request $requestModel
     * @param string $messsageText
     * @param string $owner
     */
    private function notifyAboutNewMessage(Request $requestModel, $messsageText, $owner)
    {
        $templateData = ['message' => $messsageText];
        $this->send($requestModel, null, $owner == Owner::CUSTOMER_VALUE, $templateData);
    }

    /**
     * Send email
     * If $template is null, extension config setting will be used
     *
     * @param Request $requestModel
     * @param string|null $template
     * @param bool $toAdmin
     * @param array $templateData
     */
    private function send(Request $requestModel, $template = null, $toAdmin = true, $templateData = [])
    {
        $emailConfigData = $this->getEmailConfigData($requestModel->getStoreId());
        $to = $toAdmin ?
            [$emailConfigData['department']['name'] => $emailConfigData['department']['email']] :
            [$requestModel->getCustomerName() => $requestModel->getCustomerEmail()]
        ;
        if (!$template) {
            $template = $toAdmin ?
                $emailConfigData['template']['to_admin'] :
                $emailConfigData['template']['to_customer']
            ;
        }
        $this->sender->send(
            $emailConfigData['department'],
            $to,
            array_merge(
                $templateData,
                [
                    'store_id' => $requestModel->getStoreId(),
                    'order' => $this->orderFactory->create()->load($requestModel->getOrderId()),
                    'request_model' => $requestModel,
                    'template_id' => $template
                ]
            )
        );
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    private function getEmailConfigData($storeId = null)
    {
        // todo: move to config helper
        $departmentName = $this->scopeConfig->getValue(
            self::XML_PATH_DEPARTMENT_NAME,
            StoreScope::SCOPE_STORE, $storeId
        );
        $departmentEmail = $this->scopeConfig->getValue(
            self::XML_PATH_DEPARTMENT_EMAIL,
            StoreScope::SCOPE_STORE, $storeId
        );
        if (!$departmentEmail) {
            $departmentEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                StoreScope::SCOPE_STORE, $storeId
            );
        }
        $templateToCustomer = $this->scopeConfig->getValue(
            self::XML_PATH_TEMPLATE_TO_CUSTOMER,
            StoreScope::SCOPE_STORE, $storeId
        );
        $templateToAdmin = $this->scopeConfig->getValue(
            self::XML_PATH_TEMPLATE_TO_ADMIN,
            StoreScope::SCOPE_STORE, $storeId
        );
        return [
            'department' => ['name' => $departmentName, 'email' => $departmentEmail],
            'template' => ['to_customer' => $templateToCustomer, 'to_admin' => $templateToAdmin]
        ];
    }

    /**
     * @param array $data
     */
    private function addToThread($data)
    {
        // todo: filter template
        $this->threadMessageFactory->create()
            ->setData($data)
            ->save()
        ;
    }
}