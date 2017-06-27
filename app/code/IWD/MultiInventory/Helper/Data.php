<?php

namespace IWD\MultiInventory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package IWD\MultiInventory\Helper
 */
final class Data extends AbstractHelper
{
    /**
     * Is Allow
     */
    const IS_ALLOW = 'isAllow';

    /**
     * Store
     */
    const STORE = 'store';

    /**
     * Details
     */
    const DETAILS = 'details';

    /**
     * XPath: multi inventory enable
     */
    const ENABLED = 'iwdordermanager/multi_inventory/enable';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    private $curlFactory;

    /**
     * @var null
     */
    private $response = null;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     */
    private $calculatorFactory;

    /**
     * Calculator instances for delta rounding of prices
     * @var float[]
     */
    private $calculators = [];

    /**
     * @var \Magento\Framework\Message\Session
     */
    private $session;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Math\CalculatorFactory $calculatorFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Message\Session $session
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Message\Session $session
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->curlFactory = $curlFactory;
        $this->calculatorFactory = $calculatorFactory;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getMagentoEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * @return bool
     */
    public function isExtensionEnabled()
    {
        return $this->scopeConfig->getValue(self::ENABLED) ? true : false;
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $type = 'regular', $negative = false)
    {
        if ($price) {
            if (!isset($this->calculators[$type])) {
                $this->calculators[$type] = $this->calculatorFactory->create(
                    ['scope' => $this->storeManager->getStore(true)]
                );
            }
            $price = $this->calculators[$type]->deltaRound($price, $negative);
        }
        return $price;
    }
}
