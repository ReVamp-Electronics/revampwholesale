<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Status;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesno;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->yesno = $yesno;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('awRmaStatusGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->statusCollectionFactory->create());
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
                'type' => 'aw_rma_status_name',
                'index' => 'name',
                'sortable' => false
            ]
        );
        $this->getColumn('name')
            ->setRendererType('aw_rma_status_name', 'Aheadworks\Rma\Block\Adminhtml\Status\Grid\Column\Renderer\Name')
        ;
        $this->addColumn(
            'is_email_customer',
            [
                'header' => __('Email to Customer'),
                'type' => 'options',
                'index' => 'is_email_customer',
                'options' => $this->yesno->toArray(),
                'sortable' => false
            ]
        );
        $this->addColumn(
            'is_email_admin',
            [
                'header' => __('Email to Admin'),
                'type' => 'options',
                'index' => 'is_email_admin',
                'options' => $this->yesno->toArray(),
                'sortable' => false
            ]
        );
        $this->addColumn(
            'is_thread',
            [
                'header' => __('Message to Request Thread'),
                'type' => 'options',
                'index' => 'is_thread',
                'options' => $this->yesno->toArray(),
                'sortable' => false
            ]
        );
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
