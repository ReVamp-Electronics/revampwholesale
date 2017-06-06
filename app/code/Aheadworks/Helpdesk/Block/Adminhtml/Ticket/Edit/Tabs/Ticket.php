<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs;

/**
 * Class Ticket
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs
 */
class Ticket extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Renderer for ticket items
     *
     * @var Ticket\Items
     */
    protected $ticketRenderer;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Ticket\Items $ticketRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Tabs\Ticket\Items $ticketRenderer,
        array $data = []
    ) {
        $this->ticketRenderer = $ticketRenderer;
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
        $fieldset = $form->addFieldset('ticket_fieldset', []);

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
            ->setTicketId($ticketModel->getId())
            ->setCustomerId($ticketModel->getCustomerId())
            ->setCustomerEmail($ticketModel->getCustomerEmail())
            ->setRenderer($this->ticketRenderer)
        ;

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
