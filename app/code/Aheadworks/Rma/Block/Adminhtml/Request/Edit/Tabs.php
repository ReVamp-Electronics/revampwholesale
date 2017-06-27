<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Initialize Tabs
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Request Data'));
    }

    /**
     * Prepare Layout Content
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs\General',
                    'aw_rma_edit_tabs_general'
                )->toHtml()
            ]
        );
        $this->addTab(
            'request',
            [
                'label' => __('Request'),
                'content' => $this->getLayout()->createBlock(
                    'Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs\Request',
                    'aw_rma_edit_tabs_request'
                )->toHtml()
            ]
        );
        $this->addTab(
            'products',
            [
                'label' => __('Product(s)'),
                'content' => $this->getLayout()->createBlock(
                    'Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs\Products',
                    'aw_rma_edit_tabs_products'
                )->toHtml()
            ]
        );
        if ($customer = $this->coreRegistry->registry("aw_rma_request")->getCustomer()) {
            $customerName = $customer->getName();
        } else {
            $customerName = $this->coreRegistry->registry("aw_rma_request")->getCustomerName();
        }
        $this->addTab(
            'customer',
            [
                'label' => $customerName,
                'content' => $this->getLayout()->createBlock(
                    'Aheadworks\Rma\Block\Adminhtml\Request\Edit\Tabs\Customer',
                    'aw_rma_edit_tabs_customer'
                )->toHtml()
            ]
        );
        return parent::_prepareLayout();
    }
}