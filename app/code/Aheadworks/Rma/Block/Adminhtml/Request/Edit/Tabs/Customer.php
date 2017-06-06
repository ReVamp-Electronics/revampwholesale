<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Model\Group;

class Customer extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Customer group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Order collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param AccountManagementInterface $accountManagement
     * @param Mapper $addressMapper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Customer\Helper\Address $addressHelper,
        AccountManagementInterface $accountManagement,
        Mapper $addressMapper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->addressHelper = $addressHelper;
        $this->addressRenderer = $addressRenderer;
        $this->accountManagement = $accountManagement;
        $this->addressMapper = $addressMapper;
        $this->groupRepository = $groupRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $rmaRequest = $this->_coreRegistry->registry('aw_rma_request');
        $customerData = [];
        if ($customer = $rmaRequest->getCustomer()) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customerData = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'url' => $this->getUrl(
                    'customer/index/edit',
                    ['id' => $customer->getId()]
                ),
                'group' => $this->getCustomerGroupName($customer->getGroupId()),
                'created_at' => $this->formatDate($customer->getCreatedAt(), \IntlDateFormatter::MEDIUM),
            ];
        } else {
            $customerData = [
                'name' => $rmaRequest->getCustomerName(),
                'email' => $rmaRequest->getCustomerEmail(),
                'group' => $this->getCustomerGroupName(Group::NOT_LOGGED_IN_ID),
            ];
        }
        if ($rmaRequest->isVirtual()) {
            $customerData['address'] = $this->getBillingAddressHtml($customer ? $customer->getId() : null);
        } else {
            $customerData['address'] = $this->getShippingAddressHtml();
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('customer_');

        $fieldset = $form->addFieldset('customer_fieldset', []);
        $fieldset->addField(
            'name',
            $customer ? 'link' : 'label',
            [
                'label' => __('Name'),
                'value' => $customerData['name'],
                'href'  => $customer ? $customerData['url'] : '',
                'target' => '_blank',
            ]
        );
        $fieldset->addField(
            'email',
            'label',
            [
                'label' => __('Email'),
                'value' => $customerData['email']
            ]
        );
        $fieldset->addField(
            'contact_information',
            'note',
            [
                'label' => __('Contact Information'),
                'text' => $customerData['address']
            ]
        );
        $fieldset->addField(
            'group_name',
            'label',
            [
                'label' => __('Customer Group'),
                'value' => $customerData['group']
            ]
        );
        if ($customer) {
            $fieldset->addField(
                'customer_since',
                'label',
                [
                    'label' => __('Customer Since'),
                    'value' => $customerData['created_at']
                ]
            );
        }

        $customerStats = $this->getCustomerStats($customerData['email']);
        $fieldset->addField(
            'total_purchased_amount',
            'note',
            [
                'label' => __('Total Purchased Amount'),
                'text' => __($this->priceCurrency->format($customerStats['total_purchased_amount']))
            ]
        );
        $fieldset->addField(
            'total_orders',
            'label',
            [
                'label' => __('Total Orders'),
                'value' => __('%1 orders', $customerStats['total_orders'])
            ]
        );
        $fieldset->addField(
            'total_items',
            'label',
            [
                'label' => __('Total Items'),
                'value' => __('%1 items', $customerStats['total_items'])
            ]
        );
        $this->setForm($form);


        return parent::_prepareForm();
    }

    /**
     * Retrieve billing address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getBillingAddressHtml($customerId = null)
    {
        if ($customerId && $addressObject = $this->accountManagement->getDefaultBillingAddress($customerId)) {
            $address = $this->addressHelper
                ->getFormatTypeRenderer('html')
                ->renderArray($this->addressMapper->toFlatArray($addressObject));
        } else {
            $addressObject = $this->_coreRegistry->registry('aw_rma_request')->getOrder()->getBillingAddress();
            $address = $this->addressRenderer->format($addressObject, 'html');
        }

        if ($address === null) {
            return __('The customer does not have default billing address.');
        }
        return $address;
    }

    /**
     * @return null|string
     */
    public function getShippingAddressHtml()
    {
        $addressObject = $this->_coreRegistry->registry('aw_rma_request')->getOrder()->getShippingAddress();
        $address = $this->addressRenderer->format($addressObject, 'html');
        return $address;
    }

    /**
     * Retrieve customer group by id
     *
     * @param int $groupId
     * @return \Magento\Customer\Api\Data\GroupInterface|null
     */
    public function getCustomerGroupName($groupId)
    {
        try {
            $group = $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            return '';
        }
        return $group->getCode();
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getCustomerStats($customerId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->getSelect()
            ->reset('columns')
            ->columns([
                'total_purchased_amount'    => 'SUM(grand_total)',
                'total_orders'              => 'COUNT(*)',
                'total_items'               => 'SUM(total_item_count)'
            ])
            ->where("customer_email = ?", $customerId);
        $orderCollection->load();
        return $orderCollection->getFirstItem()->getData();
    }
}
