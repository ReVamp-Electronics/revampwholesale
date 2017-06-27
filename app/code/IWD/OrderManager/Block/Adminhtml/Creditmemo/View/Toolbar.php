<?php

namespace IWD\OrderManager\Block\Adminhtml\Creditmemo\View;

use IWD\OrderManager\Model\Creditmemo\Creditmemo;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Toolbar
 * @package IWD\OrderManager\Block\Adminhtml\Creditmemo\View
 */
class Toolbar extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var Creditmemo
     */
    private $creditmemo;

    /**
     * Toolbar constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Creditmemo $creditmemo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Creditmemo $creditmemo,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->creditmemo = $creditmemo;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->isAllowDeleteCreditmemo()) {
            $this->addDeleteButton();
        }
    }

    /**
     * @inheritdoc
     */
    protected function addDeleteButton()
    {
        $message = __('Are you sure you want to DELETE an creditmemo?');
        $url = $this->getDeleteUrl();
        $this->addButton(
            'iwd_creditmemo_delete',
            [
                'label'   => 'Delete',
                'class'   => 'delete',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')",
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isAllowDeleteCreditmemo()
    {
        $creditmemoId = $this->getCreditmemoId();
        $creditmemo = $this->creditmemo->load($creditmemoId);

        return $creditmemo->isAllowDeleteCreditmemo();
    }

    /**
     * @return string
     */
    protected function getDeleteUrl()
    {
        return $this->getUrl('iwdordermanager/creditmemo/delete', ['creditmemo_id' => $this->getCreditmemoId()]);
    }

    /**
     * @return integer
     */
    protected function getCreditmemoId()
    {
        return $this->coreRegistry->registry('current_creditmemo')->getId();
    }
}
