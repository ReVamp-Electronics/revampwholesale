<?php

namespace MW\RewardPoints\Block\Adminhtml\Report\Dashboard;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\RewardPoints\Model\Report
     */
    protected $_report;

    /**
     * @var string
     */
    protected $_template = 'MW_RewardPoints::report/dashboard.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\RewardPoints\Model\Report $report
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\RewardPoints\Model\Report $report,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_customerFactory = $customerFactory;
        $this->_report = $report;
    }

    /**
     * Setting default for every grid on dashboard
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<a'
                . ' href="'. $this->escapeUrl($url) . '"'
                . ' onclick="this.target=\'_blank\'"'
                . ' title="' . __('What is this?') . '"'
                . ' class="link-store-scope">'
                . __('What is this?')
                . '</a>';
        }

        return $html;
    }

    /**
     * @return mixed|string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }

        return $this->getUrl('*/*/dashboard', ['_current'=>true, 'period'=>null]);
    }

    /**
     * Retrieve current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getRequest()->getParam('store');
        return intval($storeId);
    }

    /**
     * @return mixed
     */
    public function getJsonPieChart()
    {
        return $this->_report->preapareCollectionPieChart();
    }

    /**
     * @return mixed
     */
    public function getMostUserPoint()
    {
        return $this->_report->prepareCollectionMostUserPoint();
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function getCustomer($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }
}