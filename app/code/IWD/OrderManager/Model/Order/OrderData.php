<?php

namespace IWD\OrderManager\Model\Order;

use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class OrderData
 * @package IWD\OrderManager\Model\Order
 */
class OrderData extends Order
{
    /**
     * @var string[]
     */
    private $params = [];

    /**
     * @param string[] $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $index
     * @return null|int|string
     */
    public function getParam($index)
    {
        $orderInfo = $this->getParams();
        if (isset($orderInfo[$index])) {
            return $orderInfo[$index];
        }
        return null;
    }

    /**
     * @param string $index
     * @param null $title
     * @param null $level
     * @return $this
     */
    private function updateOrderData($index, $title = null, $level = null)
    {
        $val = $this->getParam($index);

        if ($val != null) {
            $old = $this->getData($index);
            $this->setData($index, $val);
            $new = $this->getData($index);

            Logger::getInstance()->addChange($title, $old, $new, $level);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function updateState()
    {
        return $this->updateOrderData('state', 'State', 'order_info');
    }

    /**
     * @return $this
     */
    public function updateStatus()
    {
        return $this->updateOrderData('status', 'Status', 'order_info');
    }

    /**
     * @return $this
     */
    public function updateCreatedAt()
    {
        $createdAt = $this->getParam('created_at');

        if ($createdAt != null) {
            $old = $this->getCreatedAtFormatted(\IntlDateFormatter::MEDIUM);
            $createdAt = $this->timezone->convertConfigTimeToUtc($createdAt);
            $this->setData('created_at', $createdAt);
            $new = $this->getCreatedAtFormatted(\IntlDateFormatter::MEDIUM);
            Logger::getInstance()->addChange('Created date', $old, $new, 'order_info');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function updateStoreId()
    {
        $storeId = $this->getParam('store_id');

        if ($storeId != null) {
            $old = $this->getFullStoreName();
            $this->setData('store_id', $storeId);
            $new = $this->getFullStoreName();
            Logger::getInstance()->addChange('Purchased store', $old, $new, 'order_info');
            $this->updateStoreName();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function updateIncrementId()
    {
        $incrementId = $this->getNewIncrementId();
        Logger::getInstance()->addChange('Increment Id', $this->getIncrementId(), $incrementId, 'order_info');
        $this->setIncrementId($incrementId);

        $this->updateOrderIncrementIdForRelatedObjects();

        return $this;
    }

    /**
     * @return int|null|string
     * @throws \Exception
     */
    private function getNewIncrementId()
    {
        $incrementId = $this->getParam('increment_id');
        $incrementId = trim($incrementId);

        if ($this->getIncrementId() == $incrementId) {
            return $incrementId;
        }

        if (empty($incrementId)) {
            throw new LocalizedException(__("Order number is empty"));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $collection = $this->orderRepository->getList($searchCriteria);

        if (empty($collection)) {
            throw new LocalizedException(__("Order number #%1 is already exists", $incrementId));
        }

        return $incrementId;
    }

    /**
     * @return void
     */
    public function updateOrderIncrementIdForRelatedObjects()
    {
        $this->save();

        $invoices = $this->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $invoice->save();
        }

        $shipments = $this->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            $shipment->save();
        }

        $creditMemos = $this->getCreditmemosCollection();
        foreach ($creditMemos as $creditMemo) {
            $creditMemo->save();
        }
    }

    /**
     * @return string
     */
    private function getFullStoreName()
    {
        $store = $this->getStore();
        return $store->getWebsite()->getName()
        . ' - ' . $store->getGroup()->getName()
        . ' - ' . $store->getName();
    }

    /**
     * @return void
     */
    private function updateStoreName()
    {
        $store = $this->getStore();
        $name = [
            $store->getWebsite()->getName(),
            $store->getGroup()->getName(),
            $store->getName(),
        ];

        $this->setStoreName(implode(PHP_EOL, $name));
    }

    /**
     * @return $this
     */
    public function updateCustomerGroups()
    {
        return $this->updateOrderData('customer_group_id', 'Group id', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateCustomerEmail()
    {
        return $this->updateOrderData('customer_email', 'Customer email', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updatePrefix()
    {
        return $this->updateOrderData('customer_prefix', 'Prefix', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateFirstName()
    {
        return $this->updateOrderData('customer_firstname', 'First name', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateMiddleName()
    {
        return $this->updateOrderData('customer_middlename', 'Middle name', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateLastnameName()
    {
        return $this->updateOrderData('customer_lastname', 'Last name', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateSuffix()
    {
        return $this->updateOrderData('customer_suffix', 'Suffix', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateGender()
    {
        return $this->updateOrderData('customer_gender', 'Gender', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateTaxvat()
    {
        return $this->updateOrderData('customer_taxvat', 'Tax/VAT number', 'customer_info');
    }

    /**
     * @return $this
     */
    public function updateDateOfBirth()
    {
        $val = $this->getParam('customer_dob');

        if ($val != null) {
            $old = date('Y-m-d', strtotime($this->getData('customer_dob')));
            $this->setData('customer_dob', $val);
            $new = date('Y-m-d', strtotime($this->getData('customer_dob')));

            Logger::getInstance()->addChange('Day of birthday', $old, $new, 'customer_info');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function updateCustomerId()
    {
        $oldCustomerId = $this->getData('customer_id');

        $this->updateOrderData('customer_id');

        $newCustomerId = $this->getData('customer_id');

        if ($oldCustomerId != $newCustomerId) {
            $this->_eventManager->dispatch(
                'iwd_om_change_order_customer',
                [
                    'order_id' => $this->getEntityId(),
                    'customer_id' => $newCustomerId,
                    'old_customer_id' => $oldCustomerId
                ]
            );

            $this->updateRelatedCustomerInfo();

            $oldCustomer = $this->customerRepository->getById($oldCustomerId);
            $newCustomer = $this->customerRepository->getById($newCustomerId);
            $old = $oldCustomer->getFirstname() . ' ' . $oldCustomer->getLastname();
            $new = $newCustomer->getFirstname() . ' ' . $newCustomer->getLastname();
            Logger::getInstance()->addChange('Customer', $old, $new);
        }

        return $this;
    }

    /**
     * @return void
     */
    private function updateRelatedCustomerInfo()
    {
        //remove CustomerAddressId, because it's not correct info now and got an error for reorder
        $addresses = $this->getAddresses();
        foreach ($addresses as $address) {
            $address->setCustomerAddressId(null)->save();
        }
    }
}
