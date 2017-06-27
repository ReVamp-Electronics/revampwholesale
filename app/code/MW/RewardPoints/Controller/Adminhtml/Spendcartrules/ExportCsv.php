<?php

namespace MW\RewardPoints\Controller\Adminhtml\Spendcartrules;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\RewardPoints\Controller\Adminhtml\Spendcartrules
{
    /**
     * Export spending rules grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'spend_shopping_cart_reward_rules.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Spendcartrules\Grid'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
