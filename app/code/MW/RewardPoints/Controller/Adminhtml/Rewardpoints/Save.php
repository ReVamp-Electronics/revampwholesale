<?php

namespace MW\RewardPoints\Controller\Adminhtml\RewardPoints;

use MW\RewardPoints\Model\Type;
use MW\RewardPoints\Model\Status;

class Save extends \MW\RewardPoints\Controller\Adminhtml\RewardPoints
{
	/**
     * Save Customer Reward Points action
     */
    public function execute()
    {
        if ($_FILES['filename']['name'] != '') {
            try {
                $fileData = $_FILES;
                $fileName = $this->_objectManager->get('MW\RewardPoints\Helper\Import')
                    ->importProductPoints($fileData);

                $fp = @fopen($fileName, 'r');
                $line = 1;
                $errors = [];
                if ($fp) {
                    $websiteId = $this->getRequest()->getParam('website_id');
                    $customerModel = $this->_objectManager->get(
                        'Magento\Customer\Model\Customer'
                    );
                    $rwpCustomerModel = $this->_objectManager->get(
                        'MW\RewardPoints\Model\Customer'
                    );
                    $dataHelper = $this->_objectManager->get(
                        'MW\RewardPoints\Helper\Data'
                    );

                    while (!feof($fp)) {
                        $tmp = fgets($fp);
                        // Reading a file line by line
                        if ($line > 1) {
                            $content = str_replace('"', '', $tmp);
                            $customerInfo = explode(',', $content);

                            if (sizeof($customerInfo) >= 3 && sizeof($customerInfo) <= 4) {
                                $customer = $customerModel->setWebsiteId($websiteId)
                                    ->loadByEmail($customerInfo[1]);

                                if ($customer->getId()) {
                                    $dataHelper->checkAndInsertCustomerId($customer->getId(), 0);
                                    $_customer = $rwpCustomerModel->load($customer->getId());
                                    $customerInfo[2] = (int) trim($customerInfo[2], "\n");

                                    if (is_numeric($customerInfo[2])) {
                                        $store = $this->_objectManager->get(
                                                'Magento\Store\Model\Store'
                                            )->load($customer->getStoreId());
                                        $detail = 'Imported by Administrator';
                                        $detail_config = $dataHelper->getDefaultCommentConfig($store->getCode());
                                        if ($detail_config != '') {
                                            $detail = $detail_config;
                                        }
                                        if (sizeof($customerInfo) == 4) {
                                            $customerInfo[3] = trim($customerInfo[3], "\n");
                                            if (isset($customerInfo[3]) && $customerInfo[3] != '') {
                                                $detail = $customerInfo[3];
                                            }
                                        }

                                        $oldPoints = $_customer->getMwRewardPoint();
                                        $newPoints = $oldPoints + $customerInfo[2];

                                        if ($newPoints < 0) {
                                            $newPoints = 0;
                                        }
                                        $amount = abs($newPoints - $oldPoints);

                                        if ($amount > 0) {
                                            $results = $dataHelper->getTransactionExpiredPoints($amount, $store->getCode());
                                            $expiredDay     = $results[0];
                                            $expiredTime    = $results[1];
                                            $remainingPoint = $results[2];

                                            $_customer->setData('mw_reward_point', $newPoints);
                                            $_customer->save();
                                            $balance = $_customer->getMwRewardPoint();

                                            if ($customerInfo[2] > 0) {
                                                $typeOfTransaction = Type::ADMIN_ADDITION;
                                            } else {
                                                $typeOfTransaction = Type::ADMIN_SUBTRACT;
                                            }

                                            $historyData = [
                                                'type_of_transaction' => $typeOfTransaction,
                                                'amount' => $amount,
                                                'balance' => $balance,
                                                'transaction_detail' => $detail,
                                                'transaction_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                                                'expired_day' => $expiredDay,
                                                'expired_time' => $expiredTime,
                                                'point_remaining' => $remainingPoint,
                                                'status' => Status::COMPLETE
                                            ];
                                            $_customer->saveTransactionHistory($historyData);

                                            // Process expired points when spent point
                                            if ($customerInfo[2] < 0) {
                                                $dataHelper->processExpiredPointsWhenSpentPoints($_customer->getId(), $amount);
                                            }

                                            // Send mail when points are changed
                                            $dataHelper->sendEmailCustomerPointChanged(
                                                $_customer->getId(),
                                                $historyData,
                                                $store->getCode()
                                            );
                                        }
                                    } else {
                                        $errors[] = __('At rows %1 reward points must be numeric', $line);
                                    }
                                } else {
                                    $errors[] = __('At rows %1 customer is not avaiable', $line);
                                }
                            }
                        }
                        $line++;
                    }

                    if (sizeof($errors)) {
                        $err = __('Some errors occur while importing points') . '<br/>';
                        foreach ($errors as $error) {
                            $err .= $error . '<br/>';
                        }
                        $this->messageManager->addError($err);
                    }
                    fclose($fp);
                    @unlink($fileName);

                    $this->messageManager->addSuccess(__('Your file was imported successfully'));
                    $this->_redirect('*/member/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/import');
            }
        } else {
            $this->messageManager->addError(__('Please select a file to import'));
            $this->_redirect('*/*/import');
        }
    }
}
