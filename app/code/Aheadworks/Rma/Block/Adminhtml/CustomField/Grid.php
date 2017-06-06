<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\CustomField;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory
     */
    protected $customFieldCollectionFactory;

    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Type
     */
    protected $typeSource;

    /**
     * @var \Aheadworks\Rma\Model\Source\CustomField\Refers
     */
    protected $refersSource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory $customFieldCollectionFactory,
        \Aheadworks\Rma\Model\Source\CustomField\Refers $refersSource,
        \Aheadworks\Rma\Model\Source\CustomField\Type $typeSource,
        array $data = []
    ) {
        $this->customFieldCollectionFactory = $customFieldCollectionFactory;
        $this->refersSource = $refersSource;
        $this->typeSource = $typeSource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('awRmaCustomFieldGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->customFieldCollectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'type' => 'aw_rma_custom_field_name',
                'index' => 'name'
            ]
        );
        $this->getColumn('name')
            ->setRendererType('aw_rma_custom_field_name', 'Aheadworks\Rma\Block\Adminhtml\CustomField\Grid\Column\Renderer\Name')
        ;
        $this->addColumn(
            'type',
            [
                'header' => __('Field Type'),
                'type' => 'options',
                'index' => 'type',
                'options' => $this->typeSource->getOptions(),
                'sortable' => false
            ]
        );
        $this->addColumn(
            'refers',
            [
                'header' => __('Refers To'),
                'type' => 'options',
                'index' => 'refers',
                'options' => $this->refersSource->getOptions(),
                'sortable' => false
            ]
        );
        $this->addColumn(
            'website_ids',
            [
                'header' => __('Websites'),
                'type' => 'aw_rma_custom_field_websites',
                'index' => 'website_ids',
                'sortable' => false
            ]
        );
        $this->getColumn('website_ids')
            ->setRendererType('aw_rma_custom_field_websites', 'Aheadworks\Rma\Block\Adminhtml\CustomField\Grid\Column\Renderer\Websites')
        ;
        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return "";
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('aw_rma_admin/*/grid', ['_current' => true]);
    }
}
