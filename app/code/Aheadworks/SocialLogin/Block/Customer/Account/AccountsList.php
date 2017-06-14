<?php
namespace Aheadworks\SocialLogin\Block\Customer\Account;

use Aheadworks\SocialLogin\Api\Data\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\FactoryInterface as ProviderFactoryInterface;
use Magento\Customer\Model\Session;

/**
 * Class AccountsList
 */
class AccountsList extends \Aheadworks\SocialLogin\Block\Element\Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory
     */
    protected $accountCollectionFactory;

    /**
     * @var \Aheadworks\SocialLogin\Model\ProviderManagement
     */
    protected $providerManagement;

    /**
     * @var \Aheadworks\SocialLogin\Model\ResourceModel\Account\Collection
     */
    protected $accounts;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var \Aheadworks\SocialLogin\Model\Account\ImageProvider
     */
    private $imageProvider;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Aheadworks\SocialLogin\Model\Config\General $moduleConfig
     * @param Session $customerSession
     * @param \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Aheadworks\SocialLogin\Model\Account\ImageProvider $imageProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Aheadworks\SocialLogin\Model\Config\General $moduleConfig,
        Session $customerSession,
        \Aheadworks\SocialLogin\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Aheadworks\SocialLogin\Model\Account\ImageProvider $imageProvider,
        array $data
    ) {
        parent::__construct($context, $moduleConfig, $data);
        $this->customerSession = $customerSession;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->providerManagement = $providerManagement;
        $this->postDataHelper = $postDataHelper;
        $this->imageProvider = $imageProvider;
    }

    /**
     * Get unusages providers
     *
     * @return ProviderFactoryInterface[]
     */
    public function getUnUsagesProviders()
    {
        $providers = $this->providerManagement->getEnabledList();
        $usagesCodes = $this->getUsagesProviderCodes();
        $unUsagesProviderList = [];
        /** @var ProviderFactoryInterface $provider */
        foreach ($providers as $provider) {
            if (!in_array($provider->getConfig()->getCode(), $usagesCodes)) {
                $unUsagesProviderList[] = $provider;
            }
        }
        return $unUsagesProviderList;
    }

    /**
     * Get usages provider codes
     *
     * @return string[]
     */
    protected function getUsagesProviderCodes()
    {
        $codes = [];
        /** @var AccountInterface $account */
        foreach ($this->getAccounts() as $account) {
            $codes[] = $account->getType();
        }
        return array_unique($codes);
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
            $collection->addFieldToFilter(
                AccountInterface::CUSTOMER_ID,
                $this->customerSession->getCustomerId()
            );
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
     * @param ProviderFactoryInterface $provider
     *
     * @return string
     */
    public function getLinkUrl(ProviderFactoryInterface $provider)
    {
        return $this->getUrl('social/account/link', ['provider' => $provider->getConfig()->getCode()]);
    }

    /**
     * Get link post data
     *
     * @param ProviderFactoryInterface $provider
     * @return string
     */
    public function getLinkPostData(ProviderFactoryInterface $provider)
    {
        return $this->postDataHelper->getPostData(
            $this->getLinkUrl($provider)
        );
    }

    /**
     * Get unlink post data
     *
     * @param AccountInterface $account
     * @return string
     */
    public function getUnlinkPostData(AccountInterface $account)
    {
        return $this->postDataHelper->getPostData(
            $this->getUrl('social/account/unlink'),
            [
                'account_id' => $account->getId()
            ]
        );
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
