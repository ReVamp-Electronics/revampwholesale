<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Form\Element;

/**
 * Class Template
 * @package Aheadworks\Helpdesk\Block\Adminhtml\Automation\Edit\Form\Element
 */
class Template extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Get the Html for the element.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        if ($this->getContent()) {
            $html = $this->getContent();
        }
        return $html;
    }

    /**
     * Get the default html.
     *
     * @return mixed
     */
    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if ($html === null) {
            $html .= $this->getElementHtml();
        }
        return $html;
    }

    /**
     * Get html
     * @return mixed
     */
    public function getHtml()
    {
        return $this->getDefaultHtml();
    }
}
