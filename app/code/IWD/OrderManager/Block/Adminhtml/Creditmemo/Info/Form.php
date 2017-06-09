<?php

namespace IWD\OrderManager\Block\Adminhtml\Creditmemo\Info;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Creditmemo\Info
 */
class Form extends AbstractForm
{
    /**
     * @var \Magento\Sales\Api\Data\CreditmemoInterface
     */
    private $creditmemo;

    /**
     * @var int
     */
    private $creditmemoId;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function getCreditmemo()
    {
        if ($this->creditmemo == null) {
            $id = $this->getCreditmemoId();
            $this->creditmemo = $this->creditmemoRepository->get($id);
        }

        return $this->creditmemo;
    }

    /**
     * @param int $creditmemoId
     * @return $this
     */
    public function setCreditmemoId($creditmemoId)
    {
        $this->creditmemoId = $creditmemoId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditmemoId()
    {
        return $this->creditmemoId;
    }

    /**
     * @return string[]
     */
    public function getStatusList()
    {
        return $this->creditmemoRepository->create()->getStates();
    }
}
