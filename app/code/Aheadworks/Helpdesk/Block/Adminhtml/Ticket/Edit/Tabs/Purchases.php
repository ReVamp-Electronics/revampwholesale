<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs;

/**
 * Class Purchases
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs
 */
class Purchases extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Renderer for purchases items
     *
     * @var Purchases\Items
     */
    protected $purchasesRenderer;

    /**
     * Order repository model (by default)
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Filter builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Purchases\Items $purchasesRenderer
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Purchases\Items $purchasesRenderer,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->purchasesRenderer = $purchasesRenderer;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('ticket_');
        $fieldset = $form->addFieldset('purchases_fieldset', []);

        $orderItems = $this->getOrderItems(false);
        $totalAmount = 0;
        $totalOrders = 0;
        $totalItems = 0;

        foreach ($orderItems as $order) {
            $totalAmount += $order->getGrandTotal();
            $totalOrders++;
            $totalItems += $order->getTotalItemCount();
        }
        //Get abstract order model for call public methods
        reset($orderItems);
        $abstractOrder = current($orderItems);
        $fieldset->addField(
            'total_purchases_amount',
            'label',
            [
                'label' => __('Total Purchases Amount'),
                'title' => __('Total Purchases Amount'),
                'value' => $abstractOrder->formatPriceTxt($totalAmount)
            ]
        );

        $fieldset->addField(
            'total_orders',
            'label',
            [
                'label' => __('Total Orders'),
                'title' => __('Total Orders'),
                'value' => $totalOrders
            ]
        );

        $fieldset->addField(
            'total_items',
            'label',
            [
                'label' => __('Total Items'),
                'title' => __('Total Items'),
                'value' => $totalItems
            ]
        );

        $fieldset = $form->addFieldset('orders_fieldset', []);
        $fieldset
            ->addField(
                'purchases_items',
                'text',
                [
                    'name' => 'purchases_items',
                    'label' => __(''),
                    'title' => __('')
                ]
            )
            ->setOrderCollection($this->getOrderItems())
            ->setRenderer($this->purchasesRenderer)
        ;

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getOrderItems($withoutCurrentOrderId = true)
    {
        /** @var \Aheadworks\Helpdesk\Model\Ticket $ticketModel */
        $ticketModel = $this->_coreRegistry->registry('aw_helpdesk_ticket');

        $customerDataFilter = [];
        $notCurrentIdFilter = [];
        if ($ticketModel->getOrderId() && $withoutCurrentOrderId) {
            $notCurrentIdFilter[] = $this->filterBuilder
                ->setField(\Magento\Sales\Api\Data\OrderInterface::ENTITY_ID)
                ->setConditionType('neq')
                ->setValue($ticketModel->getOrderId())
                ->create();
        }

        $customerEmailOrder = $this->filterBuilder
            ->setField(\Magento\Sales\Api\Data\OrderInterface::CUSTOMER_EMAIL)
            ->setConditionType('eq')
            ->setValue($ticketModel->getCustomerEmail())
            ->create();
        $customerDataFilter[] = $customerEmailOrder;
        if ($ticketModel->getCustomerId()) {
            $customerIdOrder = $this->filterBuilder
                ->setField(\Magento\Sales\Api\Data\OrderInterface::CUSTOMER_ID)
                ->setConditionType('eq')
                ->setValue($ticketModel->getCustomerId())
                ->create();
            $customerDataFilter[] = $customerIdOrder;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($notCurrentIdFilter)
            ->addFilters($customerDataFilter)
            ->create();

        return $this->orderRepository->getList($searchCriteria)->getItems();
    }
}
