<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Sender
 * @package Aheadworks\Rma\Model
 */
class Sender
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Aheadworks\Rma\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Rma\Helper\Data $dataHelper
     * @param CustomFieldFactory $customFieldFactory
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Rma\Helper\Data $dataHelper,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->customFieldFactory = $customFieldFactory;
    }

    /**
     * @param array $from
     * @param array $to
     * @param array $templateData
     */
    public function send(array $from, array $to, array $templateData)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($templateData['template_id'])
            ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $templateData['store_id']])
            ->setTemplateVars($this->prepareTemplateVars($templateData))
            ->setFrom($from)
            ->addTo($to)
            ->getTransport()
            ->sendMessage()
        ;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareTemplateVars($data)
    {
        $templateVars = [];
        if (isset($data['request_model'])) {
            /** @var \Aheadworks\Rma\Model\Request $requestModel */
            $requestModel = $data['request_model'];
            $templateVars['subject'] = __('Notify about RMA %1', $requestModel->getIncrementId());

            $storeId = $requestModel->getStoreId();
            $resolution = $this->customFieldFactory->create()
                ->setStoreId($storeId)
                ->loadByName('Resolution')
            ;
            $packageCondition = $this->customFieldFactory->create()
                ->setStoreId($storeId)
                ->loadByName('Package Condition')
            ;

            $requestData = [
                'text_id' => $requestModel->getIncrementId(),
                'status_name' => $requestModel->getStatusFrontendLabel(),
                'request_type_name' => $resolution->getOptionLabelByValue($requestModel->getCustomFieldValue($resolution->getId())),
                'package_opened_label' => $packageCondition->getOptionLabelByValue($requestModel->getCustomFieldValue($packageCondition->getId())),
                'formatted_created_at' => $this->formatDate($requestModel->getCreatedAt()),
                'customer_name' => $requestModel->getCustomerName(),
                'customer_email' => $requestModel->getCustomerEmail(),
                'items' => $requestModel->getItemsCollection(true)->toArray(),
                'url' => $this->dataHelper->getRmaLink($requestModel),
                'admin_url' => $this->dataHelper->getAdminRmaLink($requestModel),
                'notify_rma_address' => $this->scopeConfig->getValue('aw_rma/contacts/department_address')
            ];
            if (isset($data['message'])) {
                $requestData['notify_comment_text'] = $data['message'];
            }
            if (isset($data['order'])) {
                $order = $data['order'];
                $requestData['order_id'] = $order->getIncrementId();
                $requestData['notify_order_admin_link'] = $this->dataHelper->getAdminOrderLink($order);
            }
            $templateVars['request'] = new \Magento\Framework\DataObject($requestData);
            $templateVars['store'] = $this->storeManager->getStore($storeId);
        }
        return $templateVars;
    }

    /**
     * @param null $date
     * @return string
     */
    protected function formatDate($date = null)
    {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime($date, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
    }
}