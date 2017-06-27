<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Export member grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'rewardpoints_member.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Member\Grid'
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
