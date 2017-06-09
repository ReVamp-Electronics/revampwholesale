<?php

namespace MW\RewardPoints\Controller\Adminhtml\Catalogrules;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\RewardPoints\Controller\Adminhtml\Catalogrules
{
    /**
     * Export catalog rules grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'catalog_reward_rules.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Catalogrules\Grid'
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
