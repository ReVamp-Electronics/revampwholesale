<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml;

/**
 * Class Preview
 * @package Aheadworks\Rma\Block\Adminhtml
 */
class Preview extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\Previewer
     */
    protected $previewer;

    /**
     * @var int|null
     */
    protected $storeId = null;

    /**
     * Preview constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Rma\Model\Previewer $previewer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Rma\Model\Previewer $previewer,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->previewer = $previewer;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        if ($this->storeId === null) {
            $stores = $this->_storeManager->getStores();
            $this->storeId = array_shift($stores)->getId();
        }
        return $this->storeId;
    }

    /**
     * @return array
     */
    protected function getTemplateData()
    {
        return [
            'template_id' => $this->coreRegistry->registry('template_id'),
            'status_id' => $this->coreRegistry->registry('status_id'),
            'to_admin' => $this->coreRegistry->registry('to_admin'),
            'store_id' => $this->getStoreId()
        ];
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->previewer->preview($this->getTemplateData())->getSenderName();
    }

    /**
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->previewer->preview($this->getTemplateData())->getSenderEmail();
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->previewer->preview($this->getTemplateData())->getRecipientName();
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->previewer->preview($this->getTemplateData())->getRecipientEmail();
    }

    /**
     * @return string
     */
    public function getMessageContent()
    {
        return $this->previewer->preview($this->getTemplateData())->getContent();
    }

    /**
     * @return string
     */
    public function getMessageSubject()
    {
        return $this->previewer->preview($this->getTemplateData())->getSubject();
    }
}
