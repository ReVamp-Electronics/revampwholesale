<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

use Magento\Store\Model\Information;
use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Send extends \MW\RewardPoints\Controller\Rewardpoints
{
    const EMAIL_TO_RECIPIENT_TEMPLATE_XML_PATH = 'rewardpoints/email_notifications/recipient_template';

    const XML_PATH_EMAIL_IDENTITY              = 'rewardpoints/email_notifications/email_sender';

    public function execute()
    {
        if (!$this->_dataHelper->allowSendRewardPointsToFriend()) {
            $this->_forward('noroute');
            return;
        }

        if ($this->getRequest()->getParams()) {
            // Get current customer
            $_customer = $this->_objectManager->get(
                'MW\RewardPoints\Model\CustomerFactory'
            )->create()->load($this->_customerSession->getCustomer()->getId());

            // Get sent points
            $point = $this->getRequest()->getParam("amount");
            if ($point < 0) {
                $point = -$point;
            }

            // Check balance points of current customer are greater than the sent points
            if ($_customer->getMwRewardPoint() >= $point) {
                $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                // Get customer information
                $customer     = $this->_objectManager->get(
                    'Magento\Customer\Model\Customer'
                )->setWebsiteId(
                    $storeManager->getWebsite()->getId()
                )->loadByEmail(
                    $this->getRequest()->getParam("email")
                );

                // Check customer sends points to him/her self
                if ($customer->getId() != $_customer->getId()) {
                    $store = $storeManager->getStore();
                    $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());

                    if ($customer->getId()) {
                        // Add reward points to friend
                        $this->_dataHelper->checkAndInsertCustomerId($customer->getId(), 0);
                        $mwCustomer = $this->_objectManager->get(
                            'MW\RewardPoints\Model\CustomerFactory'
                        )->create()->load($customer->getId());
                        $mwCustomer->addRewardPoint($point);

                        $results        = $this->_dataHelper->getTransactionExpiredPoints($point, $store->getCode());
                        $expiredDay     = $results[0];
                        $expiredTime    = $results[1];
                        $remainingPoint = $results[2];

                        $historyData = [
                            'type_of_transaction' => Type::RECIVE_FROM_FRIEND,
                            'amount'              => $point,
                            'balance'             => $mwCustomer->getMwRewardPoint(),
                            'transaction_detail'  => $_customer->getId(),
                            'transaction_time'    => $now,
                            'expired_day'         => $expiredDay,
                            'expired_time'        => $expiredTime,
                            'point_remaining'     => $remainingPoint,
                            'status'              => Status::COMPLETE
                        ];

                        $mwCustomer->saveTransactionHistory($historyData);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $customer->getId(),
                            $historyData,
                            $store->getCode()
                        );

                        // Subtract reward points of current customer
                        $_customer->addRewardPoint(-$point);
                        $historyData = [
                            'type_of_transaction' => Type::SEND_TO_FRIEND,
                            'amount'              => $point,
                            'balance'             => $_customer->getMwRewardPoint(),
                            'transaction_detail'  => $customer->getId(),
                            'transaction_time'    => $now,
                            'status'              => Status::COMPLETE
                        ];
                        $_customer->saveTransactionHistory($historyData);

                        // Process expired points when spent point
                        $this->_dataHelper->processExpiredPointsWhenSpentPoints($_customer->getId(), $point);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $_customer->getId(),
                            $historyData,
                            $store->getCode()
                        );

                        $this->messageManager->addSuccess(__('Your reward points were sent successfully'));
                        $this->_redirect('rewardpoints/rewardpoints/index');
                    } else {
                        // Subtract reward points of current customer
                        $_customer->addRewardPoint(-$point);
                        $historyData = [
                            'type_of_transaction' => Type::SEND_TO_FRIEND,
                            'amount'              => $point,
                            'balance'             => $_customer->getMwRewardPoint(),
                            'transaction_detail'  => $this->getRequest()->getPost("email"),
                            'transaction_time'    => $now,
                            'status'              => Status::PENDING
                        ];

                        $_customer->saveTransactionHistory($historyData);

                        // Process expired points when spent point
                        $this->_dataHelper->processExpiredPointsWhenSpentPoints($_customer->getId(), $point);

                        // Send mail when points changed
                        $this->_dataHelper->sendEmailCustomerPointChanged(
                            $_customer->getId(),
                            $historyData,
                            $store->getCode()
                        );

                        // Customer dose not exist
                        $this->messageManager->addSuccess(__('Your reward points were sent successfully'));
                    }

                    if ($this->_dataHelper->allowSendEmailNotifications()) {
                        // Send mail to friend
                        $storeName  = $this->_dataHelper->getStoreConfig(Information::XML_PATH_STORE_INFO_NAME);
                        $sender     = $this->_dataHelper->getStoreConfig(self::XML_PATH_EMAIL_IDENTITY);
                        $mailTo     = $this->getRequest()->getParam('email');
                        $name       = $this->getRequest()->getParam('name');
                        $template   = self::EMAIL_TO_RECIPIENT_TEMPLATE_XML_PATH;
                        $data = new \Magento\Framework\DataObject();
                        $data->setData($this->getRequest()->getParams());
                        $data->setSender($_customer->getCustomerModel());
                        $data->setData('login_link', $store->getUrl('customer/account/login'));
                        $data->setData('customer_link', $store->getUrl('rewardpoints/rewardpoints/index'));
                        $data->setData('register_link', $store->getUrl('customer/account/create'));
                        $data->setStoreName($storeName);
                        $this->_dataHelper->_sendEmailTransaction(
                            $sender,
                            $mailTo,
                            $name,
                            $template,
                            $data->getData(),
                            $store->getCode()
                        );
                    }
                } else {
                    $this->messageManager->addError(__("You can not send reward points to yourself"));
                }
            } else {
                // Current total reward points do not enough to send
                $this->messageManager->addError(__("You do not have enough points to send to your friend"));
            }
        }

        $this->_redirect('rewardpoints/rewardpoints/index');
    }
}
