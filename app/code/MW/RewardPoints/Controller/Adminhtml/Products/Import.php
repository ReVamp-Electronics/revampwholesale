<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class Import extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    /**
     * Import Reward Points action
     *
     * @return \Magento\Backend\Model\View\Result\Page
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
                    $productModel = $this->_objectManager->get(
                        'Magento\Catalog\Model\Product'
                    );
                    $productActionModel = $this->_objectManager->get(
                        'Magento\Catalog\Model\Product\Action'
                    );

                    while (!feof($fp)) {
                        $tmp = fgets($fp);
                        // Reading a file line by line
                        if ($line > 1) {
                            $content = str_replace('"', '', $tmp);
                            $productInfo = explode(',', $content);
                            if (sizeof($productInfo) == 3) {
                                if ($productInfo[0] && $productInfo[0] != '') {
                                    $product = $productModel->setWebsiteId($websiteId)->load($productInfo[0]);
                                } else if ($productInfo[1] && $productInfo[1] != '') {
                                    $product = $productModel->setWebsiteId($websiteId)->loadByAttribute(
                                        'sku',
                                        $productInfo[1]
                                    );
                                }

                                if ($product->getId()) {
                                    $productInfo[2] = (int) trim($productInfo[2], "\n");
                                    if (is_numeric($productInfo[2]) && $productInfo[2] >= 0) {
                                        if ($productInfo[2] == 0) {
                                            $productInfo[2] = '';
                                        }
                                        $productActionModel->updateAttributes(
                                            [$product->getId()],
                                            ['reward_point_product' => $productInfo[2]],
                                            0
                                        );
                                    } else {
                                        $errors[] = __('At rows %1 reward points must be numeric', $line);
                                    }
                                } else {
                                    $errors[] = __('At rows %1 product is not avaiable', $line);
                                };
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

                    $this->messageManager->addSuccess(__('Your file was imported successfuly'));
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/importProductPoints');
            }
        } else {
            $this->messageManager->addError(__('Please select a file to import'));
            $this->_redirect('*/*/importProductPoints');
        }
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products');
    }
}
