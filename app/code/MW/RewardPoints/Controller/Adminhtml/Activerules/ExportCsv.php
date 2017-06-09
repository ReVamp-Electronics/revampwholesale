<?php

namespace MW\RewardPoints\Controller\Adminhtml\Activerules;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\RewardPoints\Controller\Adminhtml\Activerules
{
    /**
     * Export customer behavior rules grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'activit_rules.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Activerules\Grid'
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
