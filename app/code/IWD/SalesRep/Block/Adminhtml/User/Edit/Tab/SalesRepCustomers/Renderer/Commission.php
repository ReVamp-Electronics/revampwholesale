<?php

namespace IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;
use Magento\Framework\DataObject;
use \IWD\SalesRep\Model\Plugin\Customer\ResourceModel\Customer\Collection as CustomerCollectionPlugin;
use \IWD\SalesRep\Model\Customer as AttachedCustomer;

/**
 * Class Commission
 * @package IWD\SalesRep\Block\Adminhtml\User\Edit\Tab\SalesRepCustomers\Renderer
 */
class Commission extends Currency
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Commission constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $storeManager, $currencyLocator, $currencyFactory, $localeCurrency, $data);
        $this->registry = $registry;
    }

    public function render(DataObject $row)
    {
        $showUpdateBtn = true;
        $adminUser = $this->registry->registry('admin_user');
        if ($adminUser !== null && is_object($adminUser)
            && $row->getData(CustomerCollectionPlugin::KEY_ASSIGNED_SALESREP_ID) !== null
            && $row->getData(CustomerCollectionPlugin::KEY_ASSIGNED_SALESREP_ID) != $adminUser->getData(\IWD\SalesRep\Model\Preference\ResourceModel\User\User::FIELD_NAME_SALESREPID)
        ) {
            $showUpdateBtn = false;
        }

        $rate = $row->getData(AttachedCustomer::COMMISSION_RATE);
        switch ($row->getData(AttachedCustomer::COMMISSION_TYPE)) {
            case AttachedCustomer::COMMISSION_TYPE_FIXED:
                $currencyCode = $this->_currencyLocator->getDefaultCurrency($this->_request);
                $rate = floatval($rate) * $this->_defaultBaseCurrency->getRate($currencyCode);
                $rate = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($rate);
                break;
            case AttachedCustomer::COMMISSION_TYPE_PERCENT:
                $rate = number_format($rate, 1);
                $rate = "$rate%";
                break;
            default:
                return '';
        }
        $updateBtn = " <a href='#' class='iwdsr-update-commission' data-customer-id='{$row->getId()}'>Update</a>";
        $html =  "<div>
            <span>$rate";
        if ($row->getData(AttachedCustomer::COMMISSION_TYPE) == 'percent') {
            $html .= " {$row->getData(AttachedCustomer::COMMISSION_APPLY_WHEN)} discounts</span>";
        }

        if ($showUpdateBtn) {
            $html .= $updateBtn;
        }

        $html .= "</div>";

        return $html;
    }
}
