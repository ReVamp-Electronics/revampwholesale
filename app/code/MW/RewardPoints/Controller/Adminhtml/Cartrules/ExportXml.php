<?php

namespace MW\RewardPoints\Controller\Adminhtml\Cartrules;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MW\RewardPoints\Controller\Adminhtml\Cartrules
{
    /**
     * Export cart rules grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'shopping_cart_reward_rules.xml';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Cartrules\Grid'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
