<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Page;

/**
 * Class Menu
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Page
 */
class Menu extends \Magento\Backend\Block\Template
{
    /**
     * Menu items
     *
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Helpdesk::page/menu.phtml';

    /**
     * Menu class name
     *
     * @var string
     */
    protected $className = 'aw-helpdesk-menu';

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
     * Get menu items
     *
     * @return array|null
     */
    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                'ticket' => [
                    'title' => __('Tickets'),
                    'url' => $this->getUrl('*/ticket/index'),
                    'resource' => 'Aheadworks_Helpdesk::tickets'
                ],
                'automation' => [
                    'title' => __('Automations'),
                    'url' => $this->getUrl('*/automation/index'),
                    'resource' => 'Aheadworks_Helpdesk::automation'
                ],
                'department' => [
                    'title' => __('Departments'),
                    'url' => $this->getUrl('*/department/index'),
                    'resource' => 'Aheadworks_Helpdesk::departments'
                ],
                'system_config' => [
                    'title' => __('Settings'),
                    'url' => $this->getUrl('adminhtml/system_config/edit', ['section' => 'aw_helpdesk'])
                ],
                'readme' => [
                    'title' => __('Readme'),
                    'url' => 'http://confluence.aheadworks.com/display/EUDOC/Help+Desk+Ultimate+-+Magento+2',
                    'attr' => [
                        'target' => '_blank'
                    ],
                    'separator' => true
                ],
                'support' => [
                    'title' => __('Get Support'),
                    'url' => 'http://ecommerce.aheadworks.com/contacts/',
                    'attr' => [
                        'target' => '_blank'
                    ]
                ]
            ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }
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
        return $items['ticket'];
    }

    /**
     * Render attributes
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
     * Is current item selected
     *
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}
