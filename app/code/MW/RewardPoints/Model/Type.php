<?php

namespace MW\RewardPoints\Model;

use MW\RewardPoints\Model\Status;

class Type extends \Magento\Framework\DataObject
{
	const REGISTERING                          = 1;
    const SUBMIT_PRODUCT_REVIEW                = 2;
    const PURCHASE_PRODUCT                     = 3;
    const INVITE_FRIEND                        = 4;
    const FRIEND_REGISTERING                   = 5;
    const FRIEND_FIRST_PURCHASE                = 6;
    const RECIVE_FROM_FRIEND                   = 7;
    const CHECKOUT_ORDER                       = 8;
    const SEND_TO_FRIEND                       = 9;
    const EXCHANGE_TO_CREDIT                   = 10;
    const USE_TO_CHECKOUT                      = 11;
    const ADMIN_ADDITION                       = 12;
    const EXCHANGE_FROM_CREDIT                 = 13;
    const FRIEND_NEXT_PURCHASE                 = 14;
    const SIGNING_UP_NEWLETTER                 = 16;
    const ADMIN_SUBTRACT                       = 17;
    const BUY_POINTS                           = 18;
    const SEND_TO_FRIEND_EXPIRED               = 19;
    const REFUND_ORDER                         = 20;
    const REFUND_ORDER_ADD_POINTS              = 21;
    const REFUND_ORDER_SUBTRACT_POINTS         = 22;
    const REFUND_ORDER_SUBTRACT_PRODUCT_POINTS = 23;
    const REFUND_ORDER_FREND_PURCHASE          = 24;
    const LIKE_FACEBOOK                        = 25;
    const CUSTOMER_BIRTHDAY                    = 26;
    const SPECIAL_EVENTS                       = 27;
    const EXPIRED_POINTS                       = 28;
    const SEND_FACEBOOK                        = 29;
    const CHECKOUT_ORDER_NEW                   = 30;
    const REFUND_ORDER_SUBTRACT_POINTS_NEW     = 31;
    const ORDER_CANCELLED_ADD_POINTS           = 32;
    const POSTING_TESTIMONIAL                  = 50;
    const CUSTOM_RULE                          = 51;
    const COUPON_CODE                          = 53;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \MW\RewardPoints\Model\ActiverulesFactory
     */
    protected $_activerulesFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MW\RewardPoints\Model\ActiverulesFactory $activerulesFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_productFactory = $productFactory;
        $this->_customerFactory = $customerFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_orderFactory = $orderFactory;
        $this->_activerulesFactory = $activerulesFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_scopeResolver = $scopeResolver;
    }

    public static function getTypeReward()
    {
        return [
            self::REGISTERING               => __('Signing Up'),
            self::SUBMIT_PRODUCT_REVIEW     => __('Posting Product Review'),
            self::INVITE_FRIEND             => __('Referral Visitor (Friend click on referral link)'),
            self::FRIEND_REGISTERING        => __('Referral Sign-Up'),
            self::FRIEND_FIRST_PURCHASE     => __('First Referral Purchase'),
            self::FRIEND_NEXT_PURCHASE      => __('Next Referral Purchases'),
            self::SIGNING_UP_NEWLETTER      => __('Signing Up Newsletter'),
            self::LIKE_FACEBOOK             => __('Facebook Like'),
            self::SEND_FACEBOOK             => __('Facebook Share'),
            self::CUSTOMER_BIRTHDAY         => __('Customer Birthday'),
            self::SPECIAL_EVENTS            => __('Special Events'),
            self::CUSTOM_RULE               => __('Custom Reward'),
        ];
    }

    public static function getOptionArray()
    {
        return [
            self::REGISTERING               => __('Signing Up'),
            self::SUBMIT_PRODUCT_REVIEW     => __('Posting Product Review'),
            self::PURCHASE_PRODUCT          => __('Purchase Product'),
            self::INVITE_FRIEND             => __('Referral Visitor (Friend click on referral link)'),
            self::FRIEND_REGISTERING        => __('Referral Sign-Up'),
            self::FRIEND_FIRST_PURCHASE     => __('First Referral Purchase'),
            self::RECIVE_FROM_FRIEND        => __('Receive From Friend'),
            self::SEND_TO_FRIEND            => __('Send Points To Friend'),
            self::CHECKOUT_ORDER            => __('Checkout An Order'),
            self::CHECKOUT_ORDER_NEW        => __('Checkout An Order'),
            self::EXCHANGE_TO_CREDIT        => __('Exchange To Credit'),
            self::USE_TO_CHECKOUT           => __('Use To Checkout'),
            self::ADMIN_ADDITION            => __('Add By Admin'),
            self::ADMIN_SUBTRACT            => __('Subtract By Admin'),
            self::EXCHANGE_FROM_CREDIT      => __('Exchange From Credit'),
            self::FRIEND_NEXT_PURCHASE      => __('Next Referral Purchases'),
            self::SIGNING_UP_NEWLETTER      => __('Signing Up Newsletter'),
            self::BUY_POINTS                => __('Buy Reward Points'),
            self::SEND_TO_FRIEND_EXPIRED    => __('Send Points To Friend'),
            self::LIKE_FACEBOOK             => __('Facebook Like'),
            self::SEND_FACEBOOK             => __('Facebook Share'),
            self::CUSTOMER_BIRTHDAY         => __('Customer Birthday'),
            self::SPECIAL_EVENTS            => __('Special Events'),
            self::POSTING_TESTIMONIAL       => __('Posting Testimonial'),
            self::CUSTOM_RULE               => __('Custom Reward'),
            self::COUPON_CODE               => __('Coupon Code')
        ];
    }

    public static function getAddPointArray()
    {
        return [
            self::REGISTERING,
            self::SUBMIT_PRODUCT_REVIEW,
            self::PURCHASE_PRODUCT,
            self::INVITE_FRIEND,
            self::FRIEND_REGISTERING,
            self::FRIEND_FIRST_PURCHASE,
            self::FRIEND_NEXT_PURCHASE,
            self::RECIVE_FROM_FRIEND,
            self::CHECKOUT_ORDER,
            self::CHECKOUT_ORDER_NEW,
            self::SIGNING_UP_NEWLETTER,
            self::ADMIN_ADDITION,
            self::BUY_POINTS,
            self::REFUND_ORDER_ADD_POINTS,
            self::ORDER_CANCELLED_ADD_POINTS,
            self::LIKE_FACEBOOK,
            self::SEND_FACEBOOK ,
            self::CUSTOMER_BIRTHDAY,
            self::SPECIAL_EVENTS,
            self::SEND_TO_FRIEND_EXPIRED,
            self::POSTING_TESTIMONIAL,
            self::CUSTOM_RULE,
            self::COUPON_CODE
        ];
    }

    public static function getSubtractPointArray()
    {
        return [
            self::SEND_TO_FRIEND,
            self::EXCHANGE_TO_CREDIT,
            self::USE_TO_CHECKOUT,
            self::ADMIN_SUBTRACT,
            self::REFUND_ORDER_SUBTRACT_POINTS,
            self::REFUND_ORDER_SUBTRACT_PRODUCT_POINTS,
            self::REFUND_ORDER_FREND_PURCHASE,
            self::EXPIRED_POINTS
        ];
    }

    public function getTransactionDetail($type, $detail = null, $status = null, $is_admin = false)
    {
        $result = "";
        switch($type) {
            case self::REGISTERING:
                $result = __("Reward for Registering");
                break;
            case self::COUPON_CODE:
                $newDetail = $this->_activerulesFactory->create()->load($detail)->getRuleName();
                $result = __("%1", $newDetail);
                break;
            case self::CUSTOM_RULE:
                $newDetail = $this->_activerulesFactory->create()->load($detail)->getRuleName();
                $result    = __("%1", $newDetail);
                break;
            case self::POSTING_TESTIMONIAL:
                $result = __("Posting Testimonial Id %1",$detail);
                break;
            case self::SUBMIT_PRODUCT_REVIEW:
                $detail = explode('|',$detail);
                $review = $this->_reviewFactory->create()->load($detail[0]);
                $object = $this->_productFactory->create();

                if ($review->getId()) {
                    $object->load($review->getEntityPkValue());
                } else {
                    $object->load($detail[1]);
                }

                $url = $object->getProductUrl();
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('catalog/product/edit', ['id'=>$object->getId()]);
                }
                $result = __("Reward for Posting Product Review <b><a href='%1'>%2</a></b>",$url, $object->getName());
                break;
            case self::PURCHASE_PRODUCT:
                $_detail = explode('|',$detail);
                $product_id = $_detail[0];
                $object = $this->_productFactory->create()->load($product_id);
                $url = $object->getProductUrl();
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('catalog/product/edit', ['id' => $product_id]);
                }
                /* order link */
                $order = $this->_orderFactory->create()->loadByIncrementId($_detail[1]);
                $order_url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $order_url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]);
                }
                $result = __("Reward for purchasing product <b><a href='%1'>%2</a></b> in order <b><a href='%3'>#%4</a></b>",$url, $object->getName(),$order_url,$_detail[1]);
                break;
            case self::INVITE_FRIEND:
                $result = __("Reward Referral Visitors: (<b>%1</b>)",$detail);
                break;
            case self::FRIEND_REGISTERING:
                $object = $this->_customerFactory->create()->load($detail);
                $result = __("Reward Referral Sign-Ups: (<b>%1</b>)",$object->getEmail());
                break;
            case self::FRIEND_FIRST_PURCHASE:
                $detail = explode('|',$detail);
                $object = $this->_customerFactory->create()->load($detail[0]);

                if ($object->getEmail() == '') {
                    $result = __("Reward for the first purchase of friend");
                } else {
                    $result = __("Reward for the first purchase of friend (<b>%1</b>)",$object->getEmail());
                }
                break;
            case self::FRIEND_NEXT_PURCHASE:
                $detail = explode('|',$detail);
                $object = $this->_customerFactory->create()->load($detail[0]);
                $result = __("Reward for purchase of a friend (<b>%1</b>)",$object->getEmail());
                break;
            case self::RECIVE_FROM_FRIEND:
                $object = $this->_customerFactory->create()->load($detail);
                $result = __("Receive points from friend (<b>%1</b>)",$object->getEmail());
                break;
            case self::SEND_TO_FRIEND:
                $email = $detail;
                if ($status == Status::COMPLETE) {
                    $object = $this->_customerFactory->create()->load($detail);
                    $email = $object->getEmail();
                }

                $result = __("Send points to friend (<b>%1</b>)",$email);
                break;
            case self::CHECKOUT_ORDER:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]);
                }
                $result = __("Reward for checkout order <b><a href='%1'>#%2</a></b>",$url,$detail);
                break;
            case self::CHECKOUT_ORDER_NEW:
                $_detail = explode('||',$detail);
                $order = $this->_orderFactory->create()->loadByIncrementId($_detail[0]);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                }
                $result = __("Reward for checkout order <b><a href='%1'>#%2</a></b><br>",$url,$_detail[0]);
                $_detail_rules    = [];
                $_detail_products = [];
                $_details         = unserialize($_detail[1]);
                if (isset($_details[1])) {
                    $_detail_rules = $_details[1];
                }
                if (isset($_details[2])) {
                    $_detail_products = $_details[2];
                }

                foreach ($_detail_rules as $_detail_rule) {
                    $_detail_rule_child = explode('|',$_detail_rule);
                    $result .= __("+%1 points (%2) <br>", $_detail_rule_child[0], trim($_detail_rule_child[1]));
                }
                foreach ($_detail_products as $_detail_product) {
                    $_detail_product_child = explode('|',$_detail_product);
                    $result .= __("+%1 points for product: %2 <br>",$_detail_product_child[0],$_detail_product_child[1]);
                }
                break;
            case self::EXCHANGE_TO_CREDIT:
                $result = __("Exchange to %1 credits",round($detail,0));
                break;
            case self::USE_TO_CHECKOUT:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                }
                $result = __("Use to checkout order <b><a href='%1'>#%2</a></b>",$url,$detail);
                break;
            case self::ADMIN_ADDITION:
                $detail = explode('|',$detail);
                if ($detail[0] == '') {
                    $detail[0]= __("Updated by Admin");
                }
                $result = __("%1",$detail[0]);
                break;
            case self::ADMIN_SUBTRACT:
                $detail = explode('|',$detail);
                if ($detail[0] == '') {
                    $detail[0] = __("Updated by Admin");
                }
                $result = __("%1",$detail[0]);
                break;
            case self::EXCHANGE_FROM_CREDIT:
                $result = __("Exchange from credit");
                break;
            case self::SIGNING_UP_NEWLETTER:
                $result = __("Reward for Signing up Newsletter");
                break;
            case self::SEND_TO_FRIEND_EXPIRED:
                $result = __("The sendding points to friend(<strong>%1</strong>) was expired",$detail);
                break;
            case self::ORDER_CANCELLED_ADD_POINTS:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('adminhtml/sales_order/view', ['order_id'=>$order->getId()]);
                }
                $result = __("Restore spent points for cancelled order <a href='%1'><strong>#%2</strong></a>",$url,$detail);
                break;
            case self::REFUND_ORDER_ADD_POINTS:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                }
                $result = __("Restore spent points for refunded order <a href='%1'><strong>#%2</strong></a>",$url,$detail);
                break;
            case self::REFUND_ORDER_SUBTRACT_POINTS:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('sales/order/view', ['order_id'=>$order->getId()]);
                }
                $result = __("Subtract reward points for refunded order <a href='%1'><strong>#%2</strong></a>",$url,$detail);
                break;
            case self::REFUND_ORDER_SUBTRACT_PRODUCT_POINTS:
                $_detail = explode('|',$detail);
                $product_id = $_detail[0];
                $object = $this->_productFactory->create()->load($product_id);
                $url = $object->getProductUrl();
                if ($is_admin) {
                    $url = $this->_urlBuilder->getUrl('catalog/product/edit', ['id'=>$product_id]);
                }
                $result = __("Subtract earned points for product <b><a href='%1'>%2</a></b> (refunded)",$url, $object->getName());
                break;
            case self::REFUND_ORDER_FREND_PURCHASE:
                $detail = explode('|',$detail);
                $object = $this->_customerFactory->create()->load($detail[0]);
                $result = __("Subtract earned points for purchase of friend (<b>%1</b>) (refunded)",$object->getEmail());
                break;
            case self::LIKE_FACEBOOK:
                $secured = $this->_scopeResolver->getScope()->isCurrentlySecure();
                if ($secured) {
                    $detail = 'https://' . $detail;
                } else {
                    $detail = 'http://' . $detail;
                }
                $result = __("Reward for Facebook Like: (<b><a href='%1'>Visit Link</a></b>)", $detail);
                break;
            case self::SEND_FACEBOOK:
                $secured = $this->_scopeResolver->getScope()->isCurrentlySecure();
                if ($secured) {
                    $detail = 'https://' . $detail;
                } else {
                    $detail = 'http://' . $detail;
                }
                $result = __("Reward for Facebook Share: (<b><a href='%1'>Visit Link</a></b>)", $detail);
                break;
            case self::CUSTOMER_BIRTHDAY:
                $result = __("Reward for Your Birthday");
                break;
            case self::SPECIAL_EVENTS:
                $result = __("Reward for Special Events: (<b>%1</b>)",$detail);
                break;
            case self::EXPIRED_POINTS:
                if($detail == '') {
                    $result = __("Subtract earned points for expriring points");
                } else {
                    $result = __("Points expiration of transaction ID #%1",$detail);
                }
                break;
        }

        return $result;
    }

    public static function getAmountWithSign($amount, $type)
    {
        $result = $amount;
        switch ($type)
        {
            case self::REGISTERING:
            case self::SUBMIT_PRODUCT_REVIEW:
            case self::PURCHASE_PRODUCT:
            case self::INVITE_FRIEND:
            case self::FRIEND_REGISTERING:
            case self::FRIEND_FIRST_PURCHASE:
            case self::FRIEND_NEXT_PURCHASE:
            case self::RECIVE_FROM_FRIEND:
            case self::CHECKOUT_ORDER:
            case self::CHECKOUT_ORDER_NEW:
            case self::SIGNING_UP_NEWLETTER:
            case self::ADMIN_ADDITION:
            case self::BUY_POINTS:
            case self::REFUND_ORDER_ADD_POINTS:
            case self::ORDER_CANCELLED_ADD_POINTS:
            case self::LIKE_FACEBOOK:
            case self::SEND_FACEBOOK:
            case self::CUSTOMER_BIRTHDAY:
            case self::SPECIAL_EVENTS:
            case self::SEND_TO_FRIEND_EXPIRED:
            case self::POSTING_TESTIMONIAL:
            case self::CUSTOM_RULE:
            case self::COUPON_CODE:
                $result = "+".$amount;
                break;
            case self::SEND_TO_FRIEND:
            case self::EXCHANGE_TO_CREDIT:
            case self::USE_TO_CHECKOUT:
            case self::ADMIN_SUBTRACT:
            case self::REFUND_ORDER_SUBTRACT_POINTS:
            case self::REFUND_ORDER_SUBTRACT_PRODUCT_POINTS:
            case self::REFUND_ORDER_FREND_PURCHASE:
            case self::EXPIRED_POINTS:
                $result = -$amount;
            break;
        }

        return $result;
    }
}
