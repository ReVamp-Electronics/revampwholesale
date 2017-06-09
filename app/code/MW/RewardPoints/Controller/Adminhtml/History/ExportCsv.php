<?php

namespace MW\RewardPoints\Controller\Adminhtml\History;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\RewardPoints\Controller\Adminhtml\History
{
    /**
     * Export transactions grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'rewardpoints_history.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\History\Grid'
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
