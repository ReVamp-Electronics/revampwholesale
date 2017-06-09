<?php

namespace IWD\AuthCIM\Model;

use Magento\Framework\Model\AbstractModel;
use IWD\AuthCIM\Api\Data\CardInterface;
use Magento\Sales\Model\Order;

/**
 * Class Card
 * @package IWD\AuthCIM\Model
 */
class Card extends AbstractModel implements CardInterface
{
    /**
     * Flag is save new CC
     */
    const IS_SAVE_CC = 'cc_save';

    /**
     * Saved CC id
     */
    const SAVED_CC_ID = 'cc_id';

    /**
     * Last 4 credit card numbers
     */
    const CARD_LAST_4 = 'cc_number';

    /**
     * CC Type
     */
    const CARD_TYPE = 'cc_type';

    /**
     * Account Type
     */
    const ACCOUNT_TYPE = 'account_type';

    /**
     * Routing Number
     */
    const ROUTING_NUMBER = 'routing_number';

    /**
     * Account Number
     */
    const ACCOUNT_NUMBER = 'account_number';

    /**
     * Name On Account
     */
    const NAME_ON_ACCOUNT = 'name_on_account';

    /**
     * eCheck Type
     */
    const ECHECK_TYPE = 'echeck_type';

    /**
     * Bank Name
     */
    const BANK_NAME = 'bank_name';

    /**
     * Opaque Descriptor
     */
    const OPAQUE_DESCRIPTION = 'opaque_descriptor';

    /**
     * Opaque Value
     */
    const OPAQUE_VALUE = 'opaque_value';

    /**
     * Opaque Number
     */
    const OPAQUE_NUMBER = 'opaque_number';

    /**
     * @var Order
     */
    private $order;

    /**
     * Card constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Order $order
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Order $order,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('IWD\AuthCIM\Model\ResourceModel\Card');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIp()
    {
        return $this->getData(self::CUSTOMER_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentId()
    {
        return $this->getData(self::PAYMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodName()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastUseDate()
    {
        return $this->getData(self::LAST_USE_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate($format = false)
    {
        if ($format) {
            $expirationDay = $this->getData(self::EXPIRATION_DATE);
            return empty($expirationDay) ? 'MM/YYYY' : date('m/Y', strtotime($expirationDay));
        }

        return $this->getData(self::EXPIRATION_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalData($key = null)
    {
        if (empty($key) && !is_string($key)) {
            return $this->getData(self::ADDITIONAL_DATA);
        }
        $data = unserialize($this->getData(self::ADDITIONAL_DATA));

        return isset($data[$key]) ? $data[$key]  : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        return $this->setData(self::HASH, $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($email)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerIp($ip)
    {
        return $this->setData(self::CUSTOMER_IP, $ip);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerProfileId($customerProfileId)
    {
        return $this->setData(self::PROFILE_ID, $customerProfileId);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentId($paymentId)
    {
        return $this->setData(self::PAYMENT_ID, $paymentId);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodName($methodName)
    {
        return $this->setData(self::METHOD, $methodName);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastUseDate($lastUseDate)
    {
        return $this->setData(self::LAST_USE_DATE, $lastUseDate);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationDate($expirationDate)
    {
        return $this->setData(self::EXPIRATION_DATE, $expirationDate);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($address)
    {
        if (is_array($address)) {
            $address = serialize($address);
        }
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function addAdditionalData($key, $value = null)
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);
        if (is_string($additionalData)) {
            $additionalData = unserialize($additionalData);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $additionalData[$k] = $v;
            }
        } else {
            $additionalData[$key] = $value;
        }

        return $this->setData(self::ADDITIONAL_DATA, serialize($additionalData));
    }

    /**
     * {@inheritdoc}
     */
    public function unsetAdditionalData($key)
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);
        if (is_string($additionalData)) {
            $additionalData = unserialize($additionalData);
        }

        if (is_array($additionalData)) {
            unset($additionalData[$key]);
        }

        return $this->setData(self::ADDITIONAL_DATA, serialize($additionalData));
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_DATE, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_DATE, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastCreditCardNumber()
    {
        $ccNumber = $this->getAdditionalData('cc_number');
        return 'XXXX-' . (empty($ccNumber) ? 'XXXX' : substr($ccNumber, -4));
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        return ($this->getExpirationDate() != '' && strtotime($this->getExpirationDate()) < time());
    }

    /**
     * {@inheritdoc}
     */
    public function isInUse()
    {
        $paymentMethod = \IWD\AuthCIM\Model\Ui\ConfigProvider::CODE;
        $cardHash = $this->getHash();

        $collection = $this->order->getResourceCollection();
        $collection->addFieldToSelect([])
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->getSelect()
            ->join(
                ['payment' => $collection->getTable("sales_order_payment")],
                'main_table.entity_id=payment.parent_id',
                []
            )->where("method='$paymentMethod' AND additional_information LIKE '%$cardHash%'");

        return $collection->getSize() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationMonth()
    {
        $expirationDay = $this->getData(self::EXPIRATION_DATE);
        return empty($expirationDay) ? '0' : date('n', strtotime($expirationDay));
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationYear()
    {
        $expirationDay = $this->getData(self::EXPIRATION_DATE);
        return empty($expirationDay) ? '0' : date('Y', strtotime($expirationDay));
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardType()
    {
        $type = $this->getAdditionalData('cc_type');

        return \IWD\AuthCIM\Helper\Data::getCreditCardType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditCardTypeCode()
    {
        $type = $this->getAdditionalData('cc_type');

        return \IWD\AuthCIM\Helper\Data::getCreditCardTypeCode($type);
    }
}
