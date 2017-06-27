<?php

namespace MW\RewardPoints\Controller\Adminhtml\Report;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportOverviewCsv extends \MW\RewardPoints\Controller\Adminhtml\Report
{
    /**
     * Export overview grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'Rewardpoint_overview.csv';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_rewardpoints_report_overview.grid',
            'grid.export'
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

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::all_transactions');
    }
}
