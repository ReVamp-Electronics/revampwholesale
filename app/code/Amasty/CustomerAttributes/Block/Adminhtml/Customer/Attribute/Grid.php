<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\Grid\AbstractGrid;

class Grid extends AbstractGrid
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\CustomerAttributes\Helper\Collection
     */
    private $helper;

    /**
     * @var \Amasty\CustomerAttributes\Helper\Config
     */
    private $helperConfig;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory,
        \Amasty\CustomerAttributes\Helper\Collection $helper,
        \Amasty\CustomerAttributes\Helper\Config $helperConfig,
        Yesno $yesno,
        array $data = []
    ) {
        $this->yesno = $yesno;
        $this->helper = $helper;
        $this->helperConfig = $helperConfig;
        $this->collectionFactory = $collectionFactory;
        $this->_module = 'amcustomerattr';
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Prepare product attributes grid collection object
     *
     * @return $this
     */
    protected function _prepareCollection()
    {

        $collection = $this->collectionFactory->create()
            ->addVisibleFilter();
        $collection = $this->helper->addFilters(
            $collection,
            'eav_attribute',
            [
                "is_user_defined = 1",
                "attribute_code != 'customer_activated' "
            ]
        );

        foreach ($collection as $attribute) {
            if ('statictext' == $attribute->getTypeInternal()) {
                $attribute->setFrontendInput('statictext');
            }
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare product attributes grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Code'),
                'sortable' => true,
                'index' => 'attribute_code'
            ]
        );

        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Label'),
                'sortable' => true,
                'index' => 'frontend_label'
            ]
        );

        $this->addColumn(
            'frontend_input',
            [
                'header' => __('Type'),
                'sortable' => true,
                'index' => 'frontend_input',
                'type' => 'options',
                'options' => $this->helperConfig->getAttributeTypes(true),
                'align' => 'center',
                'renderer' => 'Amasty\CustomerAttributes\Block\Adminhtml\Customer\Attribute\Grid\Renderer\Type',
            ]
        );

        $this->addColumn(
            'sorting_order',
            [
                'header' => __('Sorting Order'),
                'sortable' => true,
                'index' => 'sorting_order',
                'width' => '90px',
                'align' => 'right',
            ]
        );

        $this->addColumn(
            'is_used_in_grid',
            [
                'header' => __('Show on the Customers Grid'),
                'sortable' => true,
                'index' => 'is_used_in_grid',
                'type' => 'options',
                'width' => '90px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'used_in_order_grid',
            [
                'header' => __('Show on the Orders Grid'),
                'sortable' => true,
                'index' => 'used_in_order_grid',
                'type' => 'options',
                'width' => '50px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'on_order_view',
            [
                'header' => __('Show on the Order View page'),
                'sortable' => true,
                'index' => 'on_order_view',
                'type' => 'options',
                'width' => '90px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'is_visible',
            [
                'header' => __('Show on the Account Information page'),
                'sortable' => true,
                'index' => 'is_visible_on_front',
                'type' => 'options',
                'width' => '90px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'on_registration',
            [
                'header' => __('Show on the Registration page'),
                'sortable' => true,
                'index' => 'on_registration',
                'type' => 'options',
                'width' => '90px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'used_in_product_listing',
            [
                'header' => __('Show on the Billing page'),
                'sortable' => true,
                'index' => 'used_in_product_listing',
                'type' => 'options',
                'width' => '90px',
                'options' => $this->yesno->toArray(),
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getAttributeId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => [
                                'attribute_id' => $this->getAttributeId(),
                            ],
                        ],
                        'field' => 'attribute_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        $this->sortColumnsByOrder();
        return $this;
    }
}
