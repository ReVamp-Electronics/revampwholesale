<?php

namespace IWD\AuthCIM\Gateway\Request\Payment;

use IWD\AuthCIM\Model\Ui\ConfigProvider;
use IWD\AuthCIM\Model\Card;
use IWD\AuthCIM\Model\CardRepository;
use IWD\AuthCIM\Model\CustomerProfile;
use IWD\AuthCIM\Model\PaymentProfile;
use IWD\AuthCIM\Model\ShippingProfile;
use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Payment\Helper\Formatter;

use IWD\AuthCIM\Gateway\Request\AbstractRequest as RequestAbstractRequest;

/**
 * Class AbstractRequest
 * @package IWD\AuthCIM\Gateway\Request
 */
class AbstractRequest extends RequestAbstractRequest
{
    use Formatter;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \IWD\AuthCIM\Api\Data\CardInterface
     */
    private $savedCard;

    /**
     * @var Card
     */
    private $card;

    /**
     * @var CardRepository
     */
    private $cardRepository;

    /**
     * @var CustomerProfile
     */
    private $customerProfile;

    /**
     * @var PaymentProfile
     */
    private $paymentProfile;

    /**
     * @var ShippingProfile
     */
    private $shippingProfile;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param GatewayConfig $config
     * @param Card $card
     * @param CardRepository $cardRepository
     * @param CustomerProfile $customerProfile
     * @param PaymentProfile $paymentProfile
     * @param ShippingProfile $shippingProfile
     */
    public function __construct(
        GatewayConfig $config,
        OrderRepositoryInterface $orderRepository,
        Card $card,
        CardRepository $cardRepository,
        CustomerProfile $customerProfile,
        PaymentProfile $paymentProfile,
        ShippingProfile $shippingProfile
    ) {
        parent::__construct($config);

        $this->orderRepository = $orderRepository;
        $this->card = $card;
        $this->cardRepository = $cardRepository;
        $this->customerProfile = $customerProfile;
        $this->paymentProfile = $paymentProfile;
        $this->shippingProfile = $shippingProfile;
        $this->savedCard = null;
    }

    /**
     * @return \IWD\AuthCIM\Api\Data\CardInterface
     */
    public function getCard()
    {
        if ($this->savedCard == null) {
            $this->savedCard = ($this->isSavedCC()) ? $this->getSavedCard() : $this->createNewSavedCard();
        }

        return $this->savedCard;
    }

    /**
     * @return bool
     */
    public function isSavedCC()
    {
        $ccId = $this->getSavedCcId();

        return $ccId != '0' && $ccId !== 0 && $ccId != null;
    }

    /**
     * @return \IWD\AuthCIM\Model\Card
     */
    public function getSavedCard()
    {
        $ccId = $this->getSavedCcId();
        $card = $this->getCart($ccId);

        $ccNumberLast4 = $card->getAdditionalData('cc_number');
        $ccType = $card->getAdditionalData('cc_type');
        $this->getPayment()->setAdditionalInformation(Card::CARD_LAST_4, $ccNumberLast4);
        $this->getPayment()->setAdditionalInformation(Card::CARD_TYPE, $ccType);

        return $card;
    }

    /**
     * @return \IWD\AuthCIM\Api\Data\CardInterface
     */
    public function createNewSavedCard()
    {
        $payment = $this->getPayment();
        $order = $this->getOrderAdapter();

        $card = $this->addCard($payment, $order);

        $this->getPayment()->setAdditionalInformation(Card::SAVED_CC_ID, $card->getHash());

        return $card;
    }

    /**
     * @return string
     */
    public function getSavedCcId()
    {
        if ($this->getPayment()->hasData(Card::SAVED_CC_ID) && $this->getPayment()->getData(Card::SAVED_CC_ID)) {
            $id = $this->getPayment()->getData(Card::SAVED_CC_ID);
            $this->getPayment()->setAdditionalInformation(Card::SAVED_CC_ID, $id);
        }

        return $this->getPayment()->getAdditionalInformation(Card::SAVED_CC_ID);
    }

    /**
     * @param $ccId
     * @return Card
     */
    public function getCart($ccId)
    {
        return $this->cardRepository->getByHash($ccId);
    }

    /**
     * @param $payment \Magento\Payment\Model\InfoInterface
     * @param $order \Magento\Payment\Gateway\Data\OrderAdapterInterface|\IWD\AuthCIM\Gateway\Data\Order\OrderAdapter
     * @return Card
     */
    public function addCard($payment, $order)
    {
        $buildSubject = $this->getBuildSubject();
        $profileId = $this->customerProfile->createCustomerProfile($buildSubject);
        $payment->setAdditionalInformation('customer_profile', $profileId);

        $paymentId = $this->paymentProfile->createPaymentProfile($buildSubject);

        $additionalData = serialize($payment->getAdditionalInformation());

        $this->card->setId(null)
            ->setCustomerId($order->getCustomerId())
            ->setCustomerEmail($order->getBillingAddress()->getEmail())
            ->setCustomerIp($order->getRemoteIp())
            ->setCustomerProfileId($profileId)
            ->setPaymentId($paymentId)
            ->setMethodName(ConfigProvider::CODE)
            ->setIsActive($this->isSaveCC($payment))
            ->setExpirationDate($this->getExpirationDate($payment))
            ->setAddress($order->getBillingAddressArray())
            ->setAdditionalData($additionalData);

        return $this->cardRepository->save($this->card);
    }

    /**
     * @param $payment \Magento\Payment\Model\InfoInterface
     * @return bool
     */
    private function isSaveCC($payment)
    {
        return $payment->getAdditionalInformation(Card::IS_SAVE_CC);
    }

    /**
     * @param $payment \Magento\Payment\Model\InfoInterface
     * @return string
     */
    private function getExpirationDate($payment)
    {
        return sprintf(
            "%s-%s-01 00:00:00",
            $payment->getData('cc_exp_year'),
            $payment->getData('cc_exp_month')
        );
    }

    /**
     * @return null|int
     */
    public function getCustomerShippingAddressId()
    {
        if (!$this->getConfig()->getSendShippingAddress()) {
            return null;
        }

        $buildSubject = $this->getBuildSubject();
        $buildSubject['customerProfileId'] = $this->getCard()->getCustomerProfileId();

        return $this->shippingProfile->createShippingAddress($buildSubject);
    }

    /**
     * @return null
     * @throws LocalizedException
     */
    public function getCardCode()
    {
        if (!$this->getConfig()->isCvvEnabled()) {
            return null;
        }

        $cardCode = $this->getPayment()->getData('cc_cid');
        if (empty($cardCode)) {
            return null;
        }

        return $cardCode;
    }
}
