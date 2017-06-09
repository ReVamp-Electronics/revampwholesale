<?php

namespace IWD\SalesRep\Block\Adminhtml\Reports\Order;

/**
 * Class Filter
 * @package IWD\SalesRep\Block\Adminhtml\Reports\Order
 */
class Filter extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    private $orderConfig;

    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    private $salesrepHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * Filter constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \IWD\SalesRep\Helper\Data $salesrepHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->orderConfig = $orderConfig;
        $this->salesrepHelper = $salesrepHelper;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $filterData = $this->getFilterData();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => '*/*/order',
                    'method' => 'get'
                ]
            ]
        );
        $form->setHtmlIdPrefix('qqq_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField('store_ids', 'hidden', ['name' => 'store_ids']);

        $fieldset->addField(
            'from',
            'date',
            [
                'name' => 'from',
                'date_format' => $dateFormat,
                'label' => __('From'),
                'title' => __('From'),
                'required' => true,
                'value' => $filterData->getData('from'),
            ]
        );

        $fieldset->addField(
            'to',
            'date',
            [
                'name' => 'to',
                'date_format' => $dateFormat,
                'label' => __('To'),
                'title' => __('To'),
                'required' => true,
                'value' => $filterData->getData('to'),
            ]
        );

        $statuses = $this->orderConfig->create()->getStatuses();
        $values = [];
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = ['label' => __($label), 'value' => $code];
            }
        }

        $fieldset->addField(
            'show_order_statuses',
            'select',
            [
                'name' => 'show_order_statuses',
                'label' => __('Order Status'),
                'options' => ['0' => __('Any'), '1' => __('Specified')],
                'note' => __('Applies to Any of the Specified Order Statuses except canceled orders'),
                'value' => $filterData->getData('show_order_statuses'),
            ],
            'to'
        );

        $chosen = $filterData->getData('order_statuses');
        if (is_array($chosen) && count($chosen) == 1 && strpos($chosen[0], ',') !== false) {
            $chosen = explode(',', $chosen[0]);
        }

        $fieldset->addField(
            'order_statuses',
            'multiselect',
            ['name' => 'order_statuses', 'values' => $values, 'display' => 'none', 'label' => ' ', 'value' => $chosen],
            'show_order_statuses'
        );

        $salesrepFilterParams = [
            'label' => 'Sales Representative',
            'name' => 'salesrep_id',
            'required' => false,
        ];

        $salesreps = $this->salesrepHelper->getSalesrepList();
        $values = [['label' => '', 'value' => '']];
        foreach ($salesreps as $salesrepId => $name) {
            $values[] = ['label' => __($name), 'value' => $salesrepId ];
        }

        $currentUserSalesrepId = $this->authSession->getUser()->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID);
        if ($currentUserSalesrepId) {
            $values = [ [
                'value' => $currentUserSalesrepId,
                'label' => $salesreps[$currentUserSalesrepId],
            ] ];

            $filterData->setData('salesrep_id', $currentUserSalesrepId);
            $salesrepFilterParams['disabled'] = true;
        }

        $salesrepFilterParams['values'] = $values;
        $salesrepFilterParams['value'] = $filterData->getData('salesrep_id');

        $fieldset->addField(
            'salesrep_id',
            'select',
            $salesrepFilterParams
        );

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}show_order_statuses",
                'show_order_statuses'
            )->addFieldMap(
                "{$htmlIdPrefix}order_statuses",
                'order_statuses'
            )->addFieldDependence(
                'order_statuses',
                'show_order_statuses',
                '1'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
