<?php
namespace Aheadworks\SocialLogin\Block\Adminhtml\Customer\Edit\Tab\Social;

use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class AccountsList
 */
class AccountsList extends \Magento\Backend\Block\Template
{
    /**
     * @var \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory
     */
    protected $accountCollectionFactory;

    /**
     * @var \Aheadworks\SocialLogin\Model\ResourceModel\Account\Collection
     */
    protected $accounts;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Aheadworks\SocialLogin\Model\Account\ImageProvider
     */
    private $imageProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\SocialLogin\Model\Account\ImageProvider $imageProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Aheadworks\SocialLogin\Model\Account\ImageProvider $imageProvider,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->registry = $registry;
        $this->imageProvider = $imageProvider;
    }

    /**
     * Get social links accounts
     *
     * @return \Aheadworks\SocialLogin\Model\ResourceModel\Account\Collection
     */
    public function getAccounts()
    {
        if (!$this->accounts) {
            $collection = $this->initAccountCollection();
            $collection->addFieldToFilter(AccountInterface::CUSTOMER_ID, $this->getCustomerId());
            $this->accounts = $collection;
        }
        return $this->accounts;
    }

    /**
     * @return \Aheadworks\SocialLogin\Model\ResourceModel\Account\Collection
     */
    protected function initAccountCollection()
    {
        return $this->accountCollectionFactory->create();
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get account image url.
     * 
     * @param AccountInterface $account
     * @return string
     */
    public function getAccountImageUrl(AccountInterface $account)
    {
        return $this->imageProvider->getAccountImageUrl($account);
    }
}
