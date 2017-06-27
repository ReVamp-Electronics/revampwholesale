<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items\NewItem
 */
class Form extends \IWD\OrderManager\Block\Adminhtml\Order\Items\Form
{
    /**
     * @var \IWD\OrderManager\Model\Order\Item[]
     */
    private $newOrderItems;

    /**
     * @var []
     */
    private $errors = [];

    /**
     * @param \IWD\OrderManager\Model\Order\Item[] $newOrderItems
     * @return $this
     */
    public function setNewOrderItems($newOrderItems)
    {
        $this->newOrderItems = $newOrderItems;
        return $this;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item[]
     */
    public function getNewOrderItems()
    {
        return $this->newOrderItems;
    }

    /**
     * @return \IWD\OrderManager\Model\Order\Item[]
     */
    public function getItems()
    {
        return $this->getNewOrderItems();
    }

    /**
     * @param string[] $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
