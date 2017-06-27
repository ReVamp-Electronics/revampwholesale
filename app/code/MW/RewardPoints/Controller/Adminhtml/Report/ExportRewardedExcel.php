<?php

namespace MW\RewardPoints\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportRewardedExcel extends \MW\RewardPoints\Controller\Adminhtml\Report
{
    /**
     * Export rewarded grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'Rewardpoint_Rewarded.xml';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_rewardpoints_report_rewarded.grid',
            'grid.export'
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

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::rewarded');
    }
}
