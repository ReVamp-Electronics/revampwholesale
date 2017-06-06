<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

class Request extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\RequestItem\Collection
     */
    protected $itemsCollection;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\RequestItem\CollectionFactory
     */
    protected $itemsCollectionFactory;

    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection
     */
    protected $thread;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\RequestItem\CollectionFactory $itemsCollectionFactory
     * @param ResourceModel\ThreadMessage\CollectionFactory $threadMessageCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Rma\Model\ResourceModel\RequestItem\CollectionFactory $itemsCollectionFactory,
        \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\CollectionFactory $threadMessageCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->itemsCollectionFactory = $itemsCollectionFactory;
        $this->thread = $threadMessageCollectionFactory->create();
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ResourceModel\Request');
    }

    /**
     * @param string $link
     * @return $this
     */
    public function loadByExternalLink($link)
    {
        return $this->load($link, 'external_link');
    }

    /**
     * @param bool $forceReload
     * @return ResourceModel\RequestItem\Collection
     */
    public function getItemsCollection($forceReload = false)
    {
        if (!$this->itemsCollection || $forceReload) {
            $this->itemsCollection = $this->itemsCollectionFactory
                ->create()
                ->addRequestFilter($this->getId())
                ->joinOrderItem()
            ;
        }
        return $this->itemsCollection;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->getIsVirtual()) {
                return false;
            }
        }
        return true;
    }

    public function getThread()
    {
        if (!$this->thread->isLoaded()) {
            $this->thread
                ->getRequestThread($this->getId())
                ->setOrder('created_at')
                ->load()
            ;
        }
        return $this->thread;
    }

    /**
     * @return string|null
     */
    public function getIncrementId()
    {
        if (!$this->hasData('increment_id')) {
            $this->getResource()->attachIncrementId($this);
        }
        return $this->getData('increment_id');
    }

    /**
     * @param null $index
     * @return array|null
     */
    public function getCustomFields($index = null)
    {
        if ($this->getId() && !$this->hasData('custom_fields')) {
            $this->getResource()->attachCustomFieldValues($this);
        }
        return $this->getData('custom_fields', $index);
    }

    /**
     * @param int|string $id
     * @param string $default
     * @return string
     */
    public function getCustomFieldValue($id, $default = '')
    {
        if (is_numeric($id)) {
            return $this->getCustomFields($id);
        }
        $value = $this->getResource()->getCustomFieldValueByName($this, $id);
        return $value ? : $default;
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        if ($this->getId() && !$this->hasData('order')) {
            $order = $this->orderFactory->create()->load($this->getOrderId());
            $this->setData('order', $order);
        }
        return $this->getData('order');
    }

    /**
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomer()
    {
        if ($this->getCustomerId() && !$this->hasData('customer')) {
            $customer = $this->customerFactory->create()->load($this->getCustomerId());
            if ($customer->getId()) {
                $this->setData('customer', $customer);
            }
        }
        return $this->getData('customer');
    }
}