<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs;

/**
 * Class Customer
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs
 */
class Customer extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Customer resource model (by default)
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer group resource model (by default)
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * Customer address resource model (by default)
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $customerAddressRepository;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * Address mapper
     *
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $customerAddressRepository
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $customerAddressRepository,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->addressHelper = $addressHelper;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Aheadworks\Helpdesk\Model\Ticket $ticketModel */
        $ticketModel = $this->_coreRegistry->registry('aw_helpdesk_ticket');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('ticket_');
        $fieldset = $form->addFieldset('customer_fieldset', []);

        try {
            if ($ticketModel->getCustomerId()) {
                $customer = $this->customerRepository->getById($ticketModel->getCustomerId());
            } else {
                $customer = $this->customerRepository->get($ticketModel->getCustomerEmail(), $ticketModel->getWebsiteId());
            }
        } catch (\Exception $e) {
            $customer = null;
        }


        if ($customer) {
            $fieldset->addField(
                'customer_name',
                'link',
                [
                    'label' => __('Customer Name'),
                    'title' => __('Customer Name'),
                    'href'  => $this->getUrl(
                            'customer/index/edit',
                            ['id' => $customer->getId()]
                        ),
                    'target' => '_blank',
                    'value' => $customer->getFirstname() . " " . $customer->getLastname()
                ]
            );
        } else {
            $fieldset->addField(
                'customer_name',
                'label',
                [
                    'label' => __('Customer Name'),
                    'title' => __('Customer Name'),
                    'value' => $ticketModel->getCustomerName()
                ]
            );
        }

        $fieldset->addField(
            'customer_email',
            'label',
            [
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'value' => $ticketModel->getCustomerEmail()
            ]
        );

        if ($customer) {
            $customerGroup = $this->customerGroupRepository->getById($customer->getGroupId());
            $fieldset->addField(
                'customer_group',
                'label',
                [
                    'label' => __('Customer Group'),
                    'title' => __('Customer Group'),
                    'value' => $customerGroup->getCode()
                ]
            );

            try {
                $customerAddress = $this->customerAddressRepository->getById($customer->getDefaultBilling());
            } catch (\Exception $e) {
                $customerAddress = null;
            }

            if ($customerAddress !== null) {
                $valueHtml = $this->addressHelper->getFormatTypeRenderer(
                    'html'
                )->renderArray(
                        $this->addressMapper->toFlatArray($customerAddress)
                    );

                $fieldset->addField(
                    'customer_address',
                    'label',
                    [
                        'label' => __('Customer Address'),
                        'title' => __('Customer Address'),
                        'after_element_html' => $valueHtml
                    ]
                );
            }

            $customerCreatedAt = $this->formatDate(
                $customer->getCreatedAt(),
                \IntlDateFormatter::LONG,
                false
            );
            $fieldset->addField(
                'customer_created',
                'label',
                [
                    'label' => __('Customer Since'),
                    'title' => __('Customer Since'),
                    'value' => $customerCreatedAt
                ]
            );
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
