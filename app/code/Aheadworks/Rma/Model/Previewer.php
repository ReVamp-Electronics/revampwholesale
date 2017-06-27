<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Previewer
 * @package Aheadworks\Rma\Model
 */
class Previewer extends Sender
{
    /**
     * @var \Magento\Framework\Mail\Template\FactoryInterface
     */
    protected $templateFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ResourceModel\Request\CollectionFactory
     */
    protected $requestCollectionFactory;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var \Magento\Framework\DataObject|null
     */
    protected $preview = null;

    /**
     * @var array|null
     */
    protected $from = null;

    /**
     * @var array|null
     */
    protected $to = null;

    /**
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Rma\Helper\Data $dataHelper
     * @param CustomFieldFactory $customFieldFactory
     * @param \Magento\Framework\Mail\Template\FactoryInterface $templateFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ResourceModel\Request\CollectionFactory $requestCollectionFactory
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Rma\Helper\Data $dataHelper,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory,
        \Magento\Framework\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory $requestCollectionFactory,
        \Aheadworks\Rma\Model\StatusFactory $statusFactory
    ) {
        parent::__construct(
            $transportBuilder,
            $scopeConfig,
            $localeDate,
            $storeManager,
            $dataHelper,
            $customFieldFactory
        );
        $this->templateFactory = $templateFactory;
        $this->orderFactory = $orderFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->statusFactory = $statusFactory;
    }

    /**
     * @param array $templateData
     * @return \Magento\Framework\DataObject
     */
    public function preview(array $templateData)
    {
        if ($this->preview === null) {
            $toAdmin = $templateData['to_admin'];
            $templateVars = $this->prepareTemplateVars($templateData);
            $emailTemplate = $this->templateFactory->get($templateData['template_id'])
                ->setVars($templateVars)
                ->setOptions(['area' => Area::AREA_FRONTEND, 'store' => $templateData['store_id']])
            ;
            $this->preview = new \Magento\Framework\DataObject([
                'content' => $emailTemplate->processTemplate(),
                'subject' => $emailTemplate->getSubject(),
                'sender_name' => $this->getFrom($templateVars, $toAdmin)->getName(),
                'sender_email' => $this->getFrom($templateVars, $toAdmin)->getEmail(),
                'recipient_name' => $this->getTo($templateVars, $toAdmin)->getName(),
                'recipient_email' => $this->getTo($templateVars, $toAdmin)->getEmail()
            ]);
        }
        return $this->preview;
    }

    /**
     * @param array $templateVars
     * @param bool $toAdmin
     * @return array|\Magento\Framework\DataObject|null
     */
    protected function getFrom(array $templateVars, $toAdmin)
    {
        if ($this->from === null) {
            $this->from = new \Magento\Framework\DataObject($this->getFromData($templateVars, $toAdmin));
        }
        return $this->from;
    }

    /**
     * @param array $templateVars
     * @param bool $toAdmin
     * @return array|\Magento\Framework\DataObject|null
     */
    protected function getTo(array $templateVars, $toAdmin)
    {
        if ($this->to === null) {
            $this->to = new \Magento\Framework\DataObject($this->getFromData($templateVars, !$toAdmin));
        }
        return $this->to;
    }

    /**
     * @param array $templateVars
     * @param bool $toAdmin
     * @return array
     */
    protected function getFromData(array $templateVars, $toAdmin)
    {
        if ($toAdmin) {
            return [
                'name' => $templateVars['request']->getCustomerName(),
                'email' => $templateVars['request']->getCustomerEmail()
            ];
        }
        return $this->getDepartmentConfigData($templateVars['store']->getId());
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    protected function getDepartmentConfigData($storeId = null)
    {
        // todo: move to config helper
        $departmentName = $this->scopeConfig->getValue('aw_rma/contacts/department_name', ScopeInterface::SCOPE_STORE, $storeId);
        $departmentEmail = $this->scopeConfig->getValue('aw_rma/contacts/department_email', ScopeInterface::SCOPE_STORE, $storeId);
        if (!$departmentEmail) {
            $departmentEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE, $storeId);
        }
        return ['name' => $departmentName, 'email' => $departmentEmail];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareTemplateVars($data)
    {
        $data['store'] = $this->storeManager->getStore($data['store_id']);
        $data['message'] = 'Dummy message';
        /** @var ResourceModel\Request\Collection $requestCollection */
        $requestCollection = $this->requestCollectionFactory->create();
        $requestCollection->getSelect()
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit(1)
        ;
        if ($requestCollection->getSize()) {
            /** @var Request $requestModel */
            $requestModel = $requestCollection->getFirstItem();
            $data['request_model'] = $requestModel;
            $data['order'] = $this->orderFactory->create()->load($requestModel->getOrderId());
            return parent::prepareTemplateVars($data);
        }
        return $this->addDummyTemplateData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function addDummyTemplateData($data)
    {
        $status = $this->statusFactory->create()
            ->setStoreId($data['store_id'])
            ->load($data['status_id'])
        ;
        $requestData = [
            'text_id' => '#XXXXXXXXX',
            'status_name' => $status->getAttribute('frontend_label'),
            'request_type_name' => 'Refund',
            'package_opened_label' => 'Opened',
            'formatted_created_at' => $this->formatDate(),
            'customer_name' => 'John Doe',
            'customer_email' => 'john_doe@example.com',
            'items' => [
                'totalRecords' => 3,
                'items' => [
                    ['name' => 'Product 1', 'sku' => 'product_1', 'qty' => 1],
                    ['name' => 'Product 2', 'sku' => 'product_2', 'qty' => 2],
                    ['name' => 'Product 3', 'sku' => 'product_3', 'qty' => 3],
                ]
            ],
            'url' => '#',
            'admin_url' => '#',
            'notify_rma_address' => $this->scopeConfig->getValue('aw_rma/contacts/department_address'),
            'order_id' => '#XXXXXXXXX',
            'notify_order_admin_link' => '#'
        ];
        $data['request'] = new \Magento\Framework\DataObject($requestData);
        return $data;
    }
}