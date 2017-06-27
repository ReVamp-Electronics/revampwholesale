<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

/**
 * Class RendererAbstract
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
abstract class RendererAbstract extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $classNames = [];

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Aheadworks\Rma\Model\CustomField|\Magento\Framework\DataObject
     */
    public function getCustomField()
    {
        if (!$this->hasData('custom_field')) {
            $this->setData('custom_field', new \Magento\Framework\DataObject());
        }
        return $this->getData('custom_field');
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        if (!$this->hasData('status_id')) {
            $this->setData('status_id', 0);
        }
        return $this->getData('status_id');
    }

    /**
     * @return bool
     */
    public function getIsWrapped()
    {
        if (!$this->hasData('is_wrapped')) {
            $this->setData('is_wrapped', true);
        }
        return $this->getData('is_wrapped');
    }

    /**
     * Check whether input is visible on frontend
     *
     * @return bool
     */
    public function isVisible()
    {
        if (!in_array($this->storeManager->getWebsite()->getId(), $this->getCustomField()->getWebsiteIds())) {
            return false;
        }
        $visibleForStatusIds = $this->getCustomField()->getVisibleForStatusIds();
        if (is_array($visibleForStatusIds)) {
            return in_array($this->getStatusId(), $visibleForStatusIds) || $this->isEditable();
        }
        return false;
    }

    /**
     * Check whether input is editable on frontend
     *
     * @return bool
     */
    public function isEditable()
    {
        $editableForStatusIds = $this->getCustomField()->getEditableForStatusIds();
        if (is_array($editableForStatusIds)) {
            return in_array($this->getStatusId(), $editableForStatusIds);
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getHtmlId()
    {
        if (!$this->hasData('html_id')) {
            $this->setData('html_id', $this->getCustomField()->getName());
        }
        return $this->getData('html_id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (!$this->hasData('name')) {
            $this->setData('name', sprintf("custom_fields[%u]", $this->getCustomField()->getId()));
        }
        return $this->getData('name');
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        $classNames = $this->classNames;
        if ($this->isRequired()) {
            $classNames[] = 'required-entry';
        }
        return implode(' ', $classNames);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->getCustomField()->getIsRequired() && !$this->getIsIgnoreValidate();
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!$this->isVisible()) {
            return '';
        }
        return $this->toHtml();
    }
}
