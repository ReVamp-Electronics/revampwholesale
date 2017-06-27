<?php

namespace IWD\AuthCIM\Controller\Customer\Cards;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use IWD\AuthCIM\Model\Customer\Cards;

/**
 * Class Add
 * @package IWD\AuthCIM\Controller\Customer\Cards
 */
abstract class AbstractAction extends Action
{
    /**
     * @var Cards
     */
    private $cards;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Context $context
     * @param Cards $cards
     * @param Session $session
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Cards $cards,
        Session $session,
        PageFactory $resultPageFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);

        $this->cards = $cards;
        $this->session = $session;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Authenticate customer
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->session->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $result = $this->action();
        } catch (\Exception $e) {
            $result = ['error' => true, 'error_message' => $e->getMessage()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }

    /**
     * @return array
     */
    abstract public function action();

    /**
     * @return Cards
     */
    public function getCardModel()
    {
        return $this->cards;
    }

    /**
     * @return PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return Registry
     */
    public function getCoreRegistry()
    {
        return $this->coreRegistry;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCardHash()
    {
        $hash = $this->getRequest()->getParam('hash', null);
        if ($hash == null) {
            throw new LocalizedException(__('Payment profile hash is empty'));
        }

        return $hash;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getCustomerId()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id', null);
        if (empty($customerId)) {
            throw new LocalizedException(__('Customer id is empty'));
        }

        return $customerId;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAddressData()
    {
        $address = $this->getRequest()->getParam('address', null);
        if (empty($address)) {
            throw new LocalizedException(__('Address data is empty'));
        }

        return $address;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPaymentData()
    {
        $payment = $this->getRequest()->getParam('payment', null);
        if (empty($payment)) {
            throw new LocalizedException(__('Payment data is empty'));
        }

        return $payment;
    }
}
