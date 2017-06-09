<?php

namespace MW\RewardPoints\Controller\Adminhtml\Member;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MW\RewardPoints\Controller\Adminhtml\Member
{
    /**
     * Export member grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'rewardpoints_member.xml';
        $content = $this->_view->getLayout()->createBlock(
            'MW\RewardPoints\Block\Adminhtml\Member\Grid'
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
