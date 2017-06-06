<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs;

use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Model\Ticket\ExternalKeyEncryptor;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermisionValidator;

/**
 * Class General
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Ticket statuses source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Status
     */
    protected $statusSource;

    /**
     * Ticket priorities source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Priority
     */
    protected $prioritySource;

    /**
     * Agents source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Agent
     */
    protected $agentSource;

    /**
     * Ticket orders source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Ticket\Order
     */
    protected $orderSource;

    /**
     * Order model factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Order Model Factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResourceModel;

    /**
     * Customer model factory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Customer resource model
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResourceModel;

    /**
     * Group model factory
     *
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Group resource model
     *
     * @var \Magento\Customer\Model\ResourceModel\Group
     */
    protected $groupResourceModel;

    /**
     * Country model factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Renderer for order items
     *
     * @var General\Items
     */
    protected $itemsRenderer;

    /**
     * Frontend url builder
     *
     * @var \Magento\Framework\Url
     */
    protected $frontendUrlBuilder;

    /**
     * Departments source
     *
     * @var \Aheadworks\Helpdesk\Model\Source\Departments
     */
    protected $departmentsSource;

    /**
     * Departments repository
     *
     * @var DepartmentRepositoryInterface
     */
    protected $departmentRepository;

    /**
     * External key encryptor
     *
     * @var ExternalKeyEncryptor
     */
    protected $externalKeyEncryptor;

    /**
     * @var PermisionValidator
     */
    private $permisionValidator;

    /**
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Order $orderSource
     * @param \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentsSource
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Model\ResourceModel\Group $groupResource
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param General\Items $itemsRenderer
     * @param \Magento\Framework\Url $frontendUrlBuilder
     * @param ExternalKeyEncryptor $externalKeyEncryptor
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param PermisionValidator $permisionValidator
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\Source\Ticket\Status $statusSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Priority $prioritySource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Agent $agentSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Order $orderSource,
        \Aheadworks\Helpdesk\Model\Source\Ticket\Department $departmentsSource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Customer\Model\ResourceModel\Group $groupResource,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\General\Items $itemsRenderer,
        \Magento\Framework\Url $frontendUrlBuilder,
        ExternalKeyEncryptor $externalKeyEncryptor,
        DepartmentRepositoryInterface $departmentRepository,
        PermisionValidator $permisionValidator,
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        $this->prioritySource = $prioritySource;
        $this->agentSource = $agentSource;
        $this->orderSource = $orderSource;
        $this->departmentsSource = $departmentsSource;
        $this->orderFactory = $orderFactory;
        $this->orderResourceModel = $orderResource;
        $this->customerFactory = $customerFactory;
        $this->customerResourceModel = $customerResource;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResource;
        $this->countryFactory = $countryFactory;
        $this->itemsRenderer = $itemsRenderer;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->externalKeyEncryptor = $externalKeyEncryptor;
        $this->departmentRepository = $departmentRepository;
        $this->permisionValidator = $permisionValidator;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Aheadworks\Helpdesk\Model\Ticket $ticketModel */
        $ticketModel = $this->_coreRegistry->registry('aw_helpdesk_ticket');
        $viewOnly = !$this->permisionValidator->updateValidate($ticketModel);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('ticket_');
        $fieldset = $form->addFieldset('general_fieldset', []);

        $fieldset->addField('id', 'hidden', ['name' => 'id', 'value' => $ticketModel->getId()]);

        $fieldset->addField(
            'status',
            'label',
            [
                'name'  => 'status',
                'label' => __("Current Ticket Status"),
                'title' => __("Current Ticket Status"),
                'css_class' => $ticketModel->getStatus(),
                'value' => $this->statusSource->getOptionLabelByValue($ticketModel->getStatus())
            ]
        );

        if ($viewOnly) {
            $fieldset->addField(
                'priority',
                'label',
                [
                    'name'  => 'priority',
                    'label' => __("Priority"),
                    'title' => __("Priority"),
                    'value' => $this->prioritySource->getOptionLabelByValue($ticketModel->getPriority())
                ]
            );
        } else {
            $fieldset->addField(
                'priority',
                'select',
                [
                    'name'  => 'priority',
                    'label' => __("Priority"),
                    'title' => __("Priority"),
                    'options' => $this->prioritySource->getOptionArray(),
                    'value' => $ticketModel->getPriority(),
                ]
            );
        }


        $department = $this->departmentRepository->getById($ticketModel->getDepartmentId());
        $depValue = $department->getIsEnabled() ? $ticketModel->getDepartmentId() : 'none';
        if ($viewOnly) {
            $fieldset->addField(
                'department_id',
                'label',
                [
                    'name'  => 'department_id',
                    'label' => __("Department"),
                    'title' => __("Department"),
                    'value' => $this->departmentsSource->getOptionByValue($depValue)
                ]
            );
        } else {
            $departmentOptions = $this->departmentsSource->getAvailableOptionsForView();
            $fieldset->addField(
                'department_id',
                'select',
                [
                    'name'      => 'department_id',
                    'label'     => __("Department"),
                    'title'     => __("Department"),
                    'required'  => true,
                    'class'     => 'validate-select',
                    'options'   => $departmentOptions,
                    'value'     => $depValue,
                ]
            );
        }

        if ($viewOnly) {
            $fieldset->addField(
                'agent_id',
                'label',
                [
                    'name'  => 'agent_id',
                    'label' => __("Agent"),
                    'title' => __("Agent"),
                    'value' => $this->agentSource->getOptionLabelByValue($ticketModel->getAgentId())
                ]
            );
        } else {
            foreach ($departmentOptions as $departmentId => $departmentLabel) {
                $agentsOptions = $this->agentSource->getAvailableOptionsForDepartment($departmentId);
                $fieldset->addField(
                    'agent_id_dep' . $departmentId,
                    'select',
                    [
                        'name' => 'agent_id',
                        'label' => __('Agent'),
                        'title' => __('Agent'),
                        'options' => $agentsOptions,
                        'value' => $ticketModel->getAgentId(),
                    ]
                );
            }
        }

        $ccRecipients = implode(', ', $ticketModel->getCcRecipients());
        $fieldset->addField(
            'cc_recipients',
            $viewOnly ? 'label' : 'text',
            [
                'name'  => 'cc_recipients',
                'label' => __("CC Recipients"),
                'title' => __("CC Recipients"),
                'value' => $ccRecipients,
            ]
        );

        $store = $this->_storeManager->getStore($ticketModel->getStoreId());
        $this->frontendUrlBuilder->setScope($store);
        $fieldset->addField(
            'external_url',
            'link',
            [
                'label' => __('External Link'),
                'title' => __('External Link'),
                'href'  => $this->frontendUrlBuilder->getUrl(
                    'aw_helpdesk/ticket/external',
                    [
                        'key' => $this->externalKeyEncryptor->encrypt(
                            $ticketModel->getCustomerEmail(),
                            $ticketModel->getId()
                        ),
                        '_secure' => $store->isUrlSecure()
                    ]
                ),
                'target' => '_blank',
                'value' => __('View')
            ]

        );

        $fieldset = $form->addFieldset('order_fieldset', ['legend' => __('Order'),]);

        $viewUrlHtml = '';
        if ($ticketModel->getOrderId()) {
            $url = $this->getUrl('sales/order/view', ['order_id' => $ticketModel->getOrderId()]);
            $urlLabel = __('View');
            $viewUrlHtml = "<a href='{$url}' class='order_link' target='_blank'>{$urlLabel}</a>";
        }
        $ordersOptions = $this->orderSource->getOptionArrayByCustomerData(
            $ticketModel->getCustomerId(),
            $ticketModel->getCustomerEmail()
        );
        if ($viewOnly) {
            if ($ticketModel->getOrderId()) {
                $orderLabel = '';
                $urlLabel = '#' . $ordersOptions[$ticketModel->getOrderId()];
                $viewUrlHtml = "<a href='{$url}' class='order_link' target='_blank'>{$urlLabel}</a>";
            } else {
                $orderLabel = $ordersOptions[0];
            }
            $fieldset->addField(
                'order_id',
                'label',
                [
                    'name'  => 'orderorder_id_name',
                    'label' => __("Order Number"),
                    'title' => __("Order Number"),
                    'value' => $orderLabel,
                    'after_element_html' => $viewUrlHtml,
                ]
            );
        } else {
            $fieldset->addField(
                'order_id',
                'select',
                [
                    'name'  => 'order_id',
                    'label' => __("Order Number"),
                    'title' => __("Order Number"),
                    'options' => $ordersOptions,
                    'value' => $ticketModel->getOrderId(),
                    'after_element_html' => $viewUrlHtml . "
                    <script type='text/javascript'>
                        require(['jquery', 'awHelpdeskTicketManager'], function($){
                            var config = {
                                externalLinkSelector: '.field-order_id .addafter .order_link',
                                orderStatusSelector: '.field-order_status .control-value',
                                orderCreatedSelector: '.field-order_date .control-value',
                                orderStatusContainerSelector: '.field-order_status',
                                orderCreatedContainerSelector: '.field-order_date',
                                orderItemsContainerSelector: '.field-order_items'
                            };

                            $.awHelpdeskTicketManager.init(config);
                            $('#{$form->getHtmlIdPrefix()}'+'order_id').change(function(){
                                var orderId =  $(this).val();
                                var url = '{$this->getUrl('*/*/changeOrder')}';
                                $.awHelpdeskTicketManager.changeOrder(url, orderId);
                            });
                        });
                    </script>"
                ]
            );
        }

        if ($ticketModel->getOrderId()) {
            $orderModel = $this->orderFactory->create();
            $this->orderResourceModel->load($orderModel, $ticketModel->getOrderId());

            $fieldset->addField(
                'order_status',
                'label',
                [
                    'name'  => 'order_status',
                    'label' => __("Order Status"),
                    'title' => __("Order Status"),
                    'value' => __($orderModel->getStatusLabel())
                ]
            );

            $fieldset->addField(
                'order_date',
                'label',
                [
                    'name'  => 'order_date',
                    'label' => __("Order Date"),
                    'title' => __("Order Date"),
                    'value' => $orderModel->getCreatedAtFormatted(\IntlDateFormatter::SHORT)
                ]
            );

            $fieldset
                ->addField(
                    'order_items',
                    'text',
                    [
                        'name' => 'order_items',
                        'label' => __(''),
                        'title' => __('')
                    ]
                )
                ->setOrderModel($orderModel)
                ->setRenderer($this->itemsRenderer)
            ;
        }

        $customerModel = $this->customerFactory->create();
        $this->customerResourceModel->load($customerModel, $ticketModel->getCustomerId());
        if ($customerModel->getId()) {
            $customerGroupModel = $this->groupFactory->create();
            $this->groupResourceModel->load($customerGroupModel, $customerModel->getGroupId());

            $fieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Customer'),]);
            $fieldset->addField(
                'customer_group',
                'label',
                [
                    'name'  => 'customer_group',
                    'label' => __("Customer Group"),
                    'title' => __("Customer Group"),
                    'value' => __($customerGroupModel->getCode())
                ]
            );

            if ($customerModel->getDefaultBillingAddress()) {
                $countryCode = $customerModel->getDefaultBillingAddress()->getCountryId();
                $country = $this->countryFactory->create()->loadByCode($countryCode);

                $fieldset->addField(
                    'country_name',
                    'label',
                    [
                        'name'  => 'country_name',
                        'label' => __("Country"),
                        'title' => __("Country"),
                        'value' => __($country->getName())
                    ]
                );
            }
        }

        if (!$viewOnly) {
            $htmlIdPrefix = $form->getHtmlIdPrefix();
            $dependencyBlock = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}department_id",
                'department_id'
            );
            foreach ($departmentOptions as $departmentId => $departmentLabel) {
                $dependencyBlock->addFieldMap(
                    "{$htmlIdPrefix}agent_id_dep" . $departmentId,
                    'dep' . $departmentId
                )->addFieldDependence(
                    'dep' . $departmentId,
                    'department_id',
                    $departmentId
                );
            }
            $this->setChild(
                'form_after',
                $dependencyBlock
            );
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
