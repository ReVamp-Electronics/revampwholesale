<?php

namespace MW\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class ReferralLinkClick implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\RewardPoints\Helper\Rules
     */
    protected $_rulesHelper;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \MW\RewardPoints\Model\RewardpointshistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\RewardPoints\Model\CustomerFactory
     */
    protected $_memberFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \MW\RewardPoints\Helper\Rules $rulesHelper
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory
     * @param \MW\RewardPoints\Model\CustomerFactory $memberFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \MW\RewardPoints\Helper\Rules $rulesHelper,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \MW\RewardPoints\Model\RewardpointshistoryFactory $historyFactory,
        \MW\RewardPoints\Model\CustomerFactory $memberFactory
    ) {
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager = $cookieManager;
        $this->_dataHelper = $dataHelper;
        $this->_rulesHelper = $rulesHelper;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_historyFactory = $historyFactory;
        $this->_memberFactory = $memberFactory;
    }

    /**
     * Check referral link and declare a new event
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Dispath referral link
        $invite = $this->_request->getParam('mw_reward');
        if ($invite) {
            $this->referralLinkClick($invite, $this->_request);
            $this->_messageManager->addSuccess(__('Thank you for visiting our site'));
        }

        // Custom rules
        if (strpos($this->_request->getPathInfo(), 'mw_re_login')) {
            $this->setCookie('mw_redirect', 1, 120);
        }

        $ruleEncrypt = trim($this->_request->getParam('mw_ref'));
        if ($ruleEncrypt) {
            $data         = explode(",", base64_decode($ruleEncrypt));
            $ruleDecrypt  = $data[1];
            $emailDecrypt = $data[2];
            if ($ruleDecrypt && $emailDecrypt) {
                $front   = $observer->getEvent()->getFront();
                $request = $front->getRequest();
                $baseUrl = $request->getBaseUrl();
                $request->setRequestUri($baseUrl . '/rewardpoints/invitation/autologin');
                $request->setPathInfo();
            }
        }

        $newRuleEncrypt = trim($this->_request->getParam('mw_rule'));
        if ($newRuleEncrypt) {
            $data           = explode(",", base64_decode($newRuleEncrypt));
            $newRuleEncrypt = $data[1];
            $ruleId         = $newRuleEncrypt;
            $customerId     = $this->_customerSession->getCustomer()->getId();
            $store          = $this->_storeManager->getStore();

            if ($customerId) {
                $this->_rulesHelper->processCustomRule($customerId, Type::CUSTOM_RULE, $ruleId, $store);
            } else {
                $this->setCookie('mw_reward_rule', $ruleId, 60 * 60 * 24 * 30);
                $pointRule = $this->_activerulesFactory->create()->getPointByRuleIdNotGroup($ruleId, $store->getId());
                if ($pointRule > 0) {
                    $this->_messageManager->addSuccess(
                        __("You will be awarded %1 points. Please Login or Create New Account to receive these points.", $pointRule)
                    );
                }
            }
        }
    }

    /**
     * Add transaction for customer if this is a referral link
     *
     * @param $invite
     * @param $request
     */
    public function referralLinkClick($invite, $request)
    {
        $customerCollection = $this->_customerFactory->create()
            ->setWebsiteId($this->_storeManager->getWebsite()->getId())
            ->getCollection();
        $customerCollection->getSelect()->where("md5(email)='".$invite."'");
        $customerId = $customerCollection->getFirstItem()->getId();

        if ($customerId) {
            if (method_exists($request, 'getClientIp')) {
                $clientIP = $request->getClientIp(true);
            } else {
                $clientIP = $request->getServer('REMOTE_ADDR');
            }

            $transactions = $this->_historyFactory->create()->getCollection()
                ->addFieldToFilter('transaction_detail', $clientIP)
                ->addFieldToFilter('customer_id', $customerId);

            if (!sizeof($transactions)) {
                $this->_dataHelper->checkAndInsertCustomerId($customerId, 0);
                $_customer = $this->_memberFactory->create()->load($customerId);
                $customerGroupId = $this->_customerFactory->create()->load($customerId)->getGroupId();
                $store           = $this->_storeManager->getStore();
                $results         = $this->_activerulesFactory->create()->getResultActiveRulesExpiredPoints(
                    Type::INVITE_FRIEND,
                    $customerGroupId,
                    $store->getId()
                );
                $points          = $results[0];
                $expiredDay      = $results[1];
                $expiredTime     = $results[2];
                $remainingPoints = $results[3];

                if ($points) {
                    $_customer->addRewardPoint($points);
                    $historyData = [
                        'type_of_transaction' => Type::INVITE_FRIEND,
                        'amount' => $points,
                        'balance' => $_customer->getMwRewardPoint(),
                        'transaction_detail' => $clientIP,
                        'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                        'expired_day' => $expiredDay,
                        'expired_time' => $expiredTime,
                        'point_remaining' => $remainingPoints,
                        'status' => Status::COMPLETE
                    ];
                    $_customer->saveTransactionHistory($historyData);

                    // Send mail when points changed
                    $this->_dataHelper->sendEmailCustomerPointChangedNew(
                        $_customer->getId(),
                        $historyData,
                        $store->getCode()
                    );
                }
            }

            $this->setCookie('friend', $customerId, 3600 * 24);
        }
    }

    /**
     * Set cookie
     *
     * @param string $name
     * @param int|string $value
     * @param int $duration
     * @return void
     */
    public function setCookie($name, $value, $duration)
    {
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath('/')
            ->setHttpOnly(false);
        $this->_cookieManager->setPublicCookie($name, $value, $publicCookieMetadata);
    }
}
