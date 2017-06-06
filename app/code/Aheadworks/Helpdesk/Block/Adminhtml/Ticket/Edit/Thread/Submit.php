<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Thread;

use Aheadworks\Helpdesk\Model\Source\Ticket\Status as TicketStatus;
use Aheadworks\Helpdesk\Model\Permission\Validator as PermisionValidator;
use Aheadworks\Helpdesk\Model\Ticket;
use Magento\Framework\Registry;

/**
 * Class Submit
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Ticket\Edit\Thread
 */
class Submit extends \Magento\Backend\Block\Template
{
    /**
     * Items for select
     *
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::ticket/edit/thread/submit.phtml';

    /**
     * Class name
     *
     * @var string
     */
    protected $className = 'aw-helpdesk-submit';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var PermisionValidator
     */
    private $permisionValidator;

    /**
     * @var Ticket
     */
    private $currentTicket;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $coreRegistry,
        PermisionValidator $permisionValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->permisionValidator = $permisionValidator;

        $this->currentTicket = $this->coreRegistry->registry('aw_helpdesk_ticket');
    }

    /**
     * Get menu container class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get items
     *
     * @return array|null
     */
    public function getMenuItems()
    {
        if (
            $this->items === null &&
            $this->currentTicket &&
            $this->currentTicket->getId() &&
            $this->permisionValidator->updateValidate($this->currentTicket)
        ) {
            $items = [
                'open' => [
                    'title' => __('Save as Open'),
                    'script' => "
                        jQuery('[name = status]').val('". TicketStatus::OPEN_VALUE ."');
                        jQuery('[name = status]').closest('form').submit();
                    ",
                    'resource' => 'Aheadworks_Helpdesk::ticket_manage'
                ],
                'pending' => [
                    'title' => __('Save as Pending'),
                    'script' => "
                        jQuery('[name = status]').val('". TicketStatus::PENDING_VALUE ."');
                        jQuery('[name = status]').closest('form').submit();
                    ",
                    'resource' => 'Aheadworks_Helpdesk::ticket_manage'
                ],
                'solved' => [
                    'title' => __('Save as Solved'),
                    'script' => "
                        jQuery('[name = status]').val('". TicketStatus::SOLVED_VALUE ."');
                        jQuery('[name = status]').closest('form').submit();
                    ",
                    'resource' => 'Aheadworks_Helpdesk::ticket_manage'
                ]
            ];
            $this->items = $items;
        }
        return $this->items;
    }

    /**
     * Get current item
     *
     * @return array
     */
    public function getCurrentItem()
    {
        $items = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }
        return $items['pending'];
    }

    /**
     * Render attribute
     *
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }
        return $result;
    }

    /**
     * Is current element
     *
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
