<?php
namespace Aheadworks\SocialLogin\Model;

use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Account
 */
class Account extends \Magento\Framework\Model\AbstractModel implements AccountInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerRepository = $customerRepository;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Aheadworks\SocialLogin\Model\ResourceModel\Account');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_getData(self::ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ACCOUNT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->_getData(self::FIRST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstName($name)
    {
        return $this->setData(self::FIRST_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->_getData(self::LAST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastName($name)
    {
        return $this->setData(self::LAST_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getImagePath()
    {
        return $this->_getData(self::IMAGE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function setImagePath($path)
    {
        return $this->setData(self::IMAGE_PATH, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSocialId()
    {
        return $this->_getData(self::SOCIAL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSocialId($socialId)
    {
        return $this->setData(self::SOCIAL_ID, $socialId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
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
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->getCustomerId());
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->setCustomerId($customer->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastSignedAt()
    {
        return $this->_getData(self::LAST_SIGNED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastSignedAt($lastSignedAt)
    {
        return $this->setData(self::LAST_SIGNED_AT, $lastSignedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLastSignedAt()
    {
        return $this->setLastSignedAt($this->getNowDate());
    }

    /**
     * Get now date
     *
     * @return string
     */
    protected function getNowDate()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = $this->dateTimeFactory->create();
        return $dateTime->gmtDate();
    }
}
