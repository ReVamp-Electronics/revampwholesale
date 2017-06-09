<?php

namespace MW\RewardPoints\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportRedeemedExcel extends \MW\RewardPoints\Controller\Adminhtml\Report
{
    /**
     * Export redeemed grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'Rewardpoint_Redeemed.xml';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_rewardpoints_report_redeemed.grid',
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
        return $this->_authorization->isAllowed('MW_RewardPoints::redeemed');
    }
}
