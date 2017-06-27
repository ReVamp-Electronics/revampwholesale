<?php

namespace IWD\AuthCIM\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Directory\Model\Region;
use IWD\AuthCIM\Api\Data\CardInterface;
use IWD\AuthCIM\Api\CardRepositoryInterface;
use IWD\AuthCIM\Model\PaymentProfile;
use IWD\AuthCIM\Gateway\Config\Config;
use IWD\AuthCIM\Model\CustomerProfile;
use IWD\AuthCIM\Helper\Data as AuthCIMHelper;

/**
 * Class Cards
 * @package IWD\AuthCIM\Model\Customer
 */
class Cards
{
    /**
     * @var CardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var CardInterface
     */
    private $card;

    /**
     * @var PaymentProfile
     */
    private $paymentProfile;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $customer = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerProfile
     */
    private $customerProfile;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var []
     */
    private $customerProfileFromAuthNet = [];

    /**
     * @var \IWD\AuthCIM\Api\Data\CardInterface[]
     */
    private $customerCards = [];

    /**
     * @var Region
     */
    private $region;

    /**
     * @param CardRepositoryInterface $cardRepository
     * @param PaymentProfile $paymentProfile
     * @param CustomerProfile $customerProfile
     * @param CustomerRepositoryInterface $customerRepository
     * @param CardInterface $card
     * @param CustomerFactory $customerFactory
     * @param Config $config
     * @param Region $region
     */
    public function __construct(
        CardRepositoryInterface $cardRepository,
        PaymentProfile $paymentProfile,
        CustomerProfile $customerProfile,
        CustomerRepositoryInterface $customerRepository,
        CardInterface $card,
        CustomerFactory $customerFactory,
        Config $config,
        Region $region
    ) {
        $this->cardRepository = $cardRepository;
        $this->paymentProfile = $paymentProfile;
        $this->customerProfile = $customerProfile;
        $this->customerRepository = $customerRepository;
        $this->card = $card;
        $this->config = $config;
        $this->customerFactory = $customerFactory;
        $this->region = $region;
    }

    /**
     * @param $hash
     */
    public function deletePaymentProfile($hash)
    {
        $card = $this->cardRepository->getByHash($hash);
        $profileId = $card->getCustomerProfileId();
        $paymentId = $card->getPaymentId();

        if ($this->paymentProfile->deletePaymentProfile($profileId, $paymentId)) {
            $this->cardRepository->delete($card);
        }
    }

    /**
     * @param $customerId
     * @param $address
     * @param $payment
     */
    public function addPaymentProfile($customerId, $address, $payment)
    {
        $customerProfileId = $this->getCustomerProfileId($customerId, $address);
        $paymentProfileId = null;
        $buildSubject = $this->paymentProfile
            ->prepareBuildSubject($customerProfileId, $paymentProfileId, $address, $payment);

        $paymentProfileId = $this->paymentProfile->createPaymentProfile($buildSubject);

        $this->addCard($customerId, $customerProfileId, $paymentProfileId, $payment, $address);
    }

    /**
     * @param $customerId
     * @param $address
     * @return array|\Magento\Framework\Api\AttributeInterface|null|string
     */
    public function getCustomerProfileId($customerId, $address = null)
    {
        if ($customerId == 0) {
            return null;
        }

        $customer = $this->getCustomer($customerId);
        $customerProfileId = $customer->getCustomAttribute('iwd_authcim_profile_id');
        $customerProfileId = !empty($customerProfileId) ? $customerProfileId->getValue() : '';

        if (empty($customerProfileId)) {
            $customerProfileId = $this->tryToFindCustomerProfileIdInExistingCards($customerId);
            if (empty($customerProfileId) && $address != null) {
                $address['email'] = $customer->getEmail();
                $customerProfileId = $this->createCustomerProfile($customerId, $address);
            }
            $customer->setCustomAttribute('iwd_authcim_profile_id', $customerProfileId);
            $this->customerRepository->save($customer);
        }

        return $customerProfileId;
    }

    /**
     * @param $payment
     * @return string
     */
    private function getPrepareAdditionalData($payment)
    {
        $additionalData = $payment;
        $additionalData['cc_number'] = $this->getCcLastFourNumber($payment);
        $additionalData['cc_save'] = isset($payment['cc_save']) ? $payment['cc_save'] : 0;
        $additionalData['method_title'] = $this->config->getTitle();
        $additionalData['cc_type'] = isset($payment['cc_type'])
            ? AuthCIMHelper::getCreditCardTypeCode($payment['cc_type'])
            : 'XX';

        return $additionalData;
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    private function getCustomer($customerId)
    {
        if ($this->customer == null && $customerId != 0) {
            $this->customer = $this->customerRepository->getById($customerId);
        }
        return $this->customer;
    }

    /**
     * @param $customerId
     * @return string
     */
    private function tryToFindCustomerProfileIdInExistingCards($customerId)
    {
        $cards = $this->cardRepository->getListForCustomer($customerId, null)->getItems();
        if (isset($cards[0])) {
            return $cards[0]->getCustomerProfileId();
        }

        return '';
    }

    /**
     * @param $customerId
     * @param $address
     * @return array|null|string
     */
    private function createCustomerProfile($customerId, $address)
    {
        $buildSubject = $this->customerProfile->prepareBuildSubject($customerId, $address);
        return $this->customerProfile->createCustomerProfile($buildSubject);
    }

    /**
     * @param $hash
     * @param $address
     * @param $payment
     */
    public function updatePaymentProfile($hash, $address, $payment)
    {
        $card = $this->cardRepository->getByHash($hash);

        $customerProfileId = $card->getCustomerProfileId();
        $paymentProfileId = $card->getPaymentId();
        $buildSubject = $this->paymentProfile
            ->prepareBuildSubject($customerProfileId, $paymentProfileId, $address, $payment);

        $this->paymentProfile->updatePaymentProfile($buildSubject);
        $payment['cc_number'] = $this->getCcLastFourNumber($payment);

        $this->updateCard($card, $payment, $address);
    }

    private function getCcLastFourNumber($payment)
    {
        return isset($payment['cc_number'])
            ? substr($payment['cc_number'], -4)
            : (isset($payment['opaque_number'])
                ? $payment['opaque_number']
                : 'xxxx'
            );
    }

    /**
     * @param $payment
     * @return null|string
     */
    private function getExpirationDate($payment)
    {
        if (!isset($payment['cc_exp_year']) || !isset($payment['cc_exp_month'])) {
            return null;
        }

        return sprintf("%s-%s-01 00:00:00", $payment['cc_exp_year'], $payment['cc_exp_month']);
    }

    /**
     * Admin can change status (visibility for checkout) of saved credit card
     *
     * @param $hash
     * @param $status
     */
    public function statusPaymentProfile($hash, $status)
    {
        $status = $status == 'true' ? true : ($status == 'false' ? false : $status);
        $status = boolval($status) ? 1 : 0;

        $card = $this->cardRepository->getByHash($hash);
        $card->setIsActive($status);
        $this->cardRepository->save($card);
    }

    /**
     * @param $customerProfileId
     * @param null $customerId
     * @throws LocalizedException
     */
    public function syncCustomerProfile($customerProfileId, $customerId = null)
    {
        if ($customerProfileId == null) {
            return;
        }

        $customerProfile = $this->getCustomerProfileFromAuthorizeNet($customerProfileId);
        if ($customerProfile === null) {
            $this->removeCustomerProfile($customerId);
            return;
        }

        $paymentProfiles = $this->getCustomerPaymentProfilesFromAuthorizeNet($customerProfileId);

        $customerId = ($customerId == null) ? $this->getCustomerMerchantCustomerId($customerProfileId) : $customerId;
        $this->customerCards = $this->cardRepository->getListForCustomer($customerId, null)->getItems();

        foreach ($paymentProfiles as $paymentProfile) {
            $paymentId = $paymentProfile['customerPaymentProfileId'];

            $card = $this->getCardByPaymentId($paymentId);

            $address = $this->prepareAddressArray($paymentProfile);
            $payment = $this->preparePaymentArray($paymentProfile, $card);

            if (!empty($card)) {
                $this->updateCard($card, $payment, $address);
            } else {
                $this->addCard($customerId, $customerProfileId, $paymentId, $payment, $address, 0);
            }
        }

        $this->removeInactiveCards();
    }

    public function removeCustomerProfile($customerId)
    {
        $this->customerCards = $this->cardRepository->getListForCustomer($customerId, null)->getItems();
        $this->removeInactiveCards();

        $customer = $this->getCustomer($customerId);
        $customer->setCustomAttribute('iwd_authcim_profile_id', '');
        $this->customerRepository->save($customer);
    }

    /**
     * @param $paymentProfile
     * @param \IWD\AuthCIM\Api\Data\CardInterface|null $card
     * @return array
     */
    private function preparePaymentArray($paymentProfile, $card)
    {
        $cardNumber = $paymentProfile['payment']['creditCard']['cardNumber'];
        $cardType = $paymentProfile['payment']['creditCard']['cardType'];
        $cardType = AuthCIMHelper::getCreditCardTypeCode($cardType);

        $payment = [
            'cc_type' =>  $cardType,
            'cc_number' => substr($cardNumber, -4),
            'method_title' => $this->config->getTitle()
        ];

        if (empty($card)) {
            $payment["cc_exp_month"] = null;
            $payment["cc_exp_year"] = null;
            $payment["cc_save"] = 0;
        } else {
            $ccTypeMage = AuthCIMHelper::getCreditCardTypeCode($card->getAdditionalData('cc_type'));
            $ccTypeAuth = AuthCIMHelper::getCreditCardTypeCode($payment['cc_type']);
            $ccNumberMage = substr($card->getAdditionalData('cc_number'), -4);
            $ccNumberAuth = substr($payment['cc_number'], -4);

            if (!empty($card) && ($ccTypeMage == $ccTypeAuth) && ($ccNumberMage == $ccNumberAuth)) {
                $payment["cc_exp_month"] = $card->getAdditionalData('cc_exp_month');
                $payment["cc_exp_year"] = $card->getAdditionalData('cc_exp_year');
            }
        }

        return $payment;
    }

    /**
     * @param $paymentProfile
     * @return array
     */
    private function prepareAddressArray($paymentProfile)
    {
        $address = $paymentProfile['billTo'];
        $addressArray = [];

        $map = [
            'firstname' => 'firstName',
            'lastname' => 'lastName',
            'company' => 'company',
            'street_line_1' => 'address',
            'city' => 'city',
            'postcode' => 'zip',
            'country_id' => 'country',
        ];
        foreach ($map as $k => $v) {
            if (isset($address[$v])) {
                $addressArray[$k] = $address[$v];
            }
        }

        if (isset($address['country']) && isset($address['state'])) {
            $region = $this->getRegionByCode($address);
            if ($region && $region->getRegionId()) {
                $addressArray['region_id'] = $region->getRegionId();
                $addressArray['region'] = $region->getName();
            } else {
                $addressArray['region'] = $address['state'];
            }
        }

        return $addressArray;
    }

    /**
     * @param $address
     * @return Region
     */
    private function getRegionByCode($address)
    {
        try {
            $country = $address['country'];
            $state = $address['state'];
            $region = $this->region->loadByCode($state, $country);
            return $region;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $paymentId
     * @return \IWD\AuthCIM\Api\Data\CardInterface|null
     */
    private function getCardByPaymentId($paymentId)
    {
        foreach ($this->customerCards as $i => $card) {
            if ($card->getPaymentId() == $paymentId) {
                unset($this->customerCards[$i]);
                return $card;
            }
        }
        return null;
    }

    /**
     * @param $customerId
     * @param $customerProfileId
     * @param $paymentProfileId
     * @param $payment
     * @param $address
     * @param int $isActive
     */
    private function addCard($customerId, $customerProfileId, $paymentProfileId, $payment, $address, $isActive = 1)
    {
        $this->card->setId(null)
            ->setCustomerId($customerId)
            ->setCustomerEmail($this->getCustomer($customerId)->getEmail())
            ->setCustomerProfileId($customerProfileId)
            ->setPaymentId($paymentProfileId)
            ->setMethodName(\IWD\AuthCIM\Model\Ui\ConfigProvider::CODE)
            ->setIsActive($isActive)
            ->setExpirationDate($this->getExpirationDate($payment))
            ->setAddress($address)
            ->addAdditionalData($this->getPrepareAdditionalData($payment));

        $this->cardRepository->save($this->card);
    }

    /**
     * @param $card CardInterface
     * @param $payment
     * @param $address
     */
    private function updateCard($card, $payment, $address)
    {
        $card->setExpirationDate($this->getExpirationDate($payment))
            ->setAddress($address)
            ->addAdditionalData($payment);

        $this->cardRepository->save($card);
    }

    private function removeInactiveCards()
    {
        foreach ($this->customerCards as $i => $card) {
            $this->cardRepository->delete($card);
        }
    }

    /**
     * @param $customerProfileId
     * @return mixed
     * @throws LocalizedException
     */
    private function getCustomerPaymentProfilesFromAuthorizeNet($customerProfileId)
    {
        $customerProfile = $this->getCustomerProfileFromAuthorizeNet($customerProfileId);

        if (!isset($customerProfile['paymentProfiles'])) {
            return [];
        }

        if (isset($customerProfile['paymentProfiles'][0])) {
            return $customerProfile['paymentProfiles'];
        }

        return [$customerProfile['paymentProfiles']];
    }

    /**
     * @param $customerProfileId
     * @return mixed
     * @throws LocalizedException
     */
    private function getCustomerMerchantCustomerId($customerProfileId)
    {
        $customerProfile = $this->getCustomerProfileFromAuthorizeNet($customerProfileId);
        if ($customerProfile == null || !isset($customerProfile['merchantCustomerId'])) {
            throw new LocalizedException(__('Merchant customer id do not exists'));
        }

        return $customerProfile['merchantCustomerId'];
    }

    /**
     * @param $customerProfileId
     * @return array|null|string
     */
    private function getCustomerProfileFromAuthorizeNet($customerProfileId)
    {
        if (empty($this->customerProfileFromAuthNet)) {
            try {
                $this->customerProfileFromAuthNet = $this->customerProfile
                    ->getCustomerProfileRequest($customerProfileId);
            } catch (\Exception $e) {
                return [];
            }
        }

        return $this->customerProfileFromAuthNet;
    }
}
