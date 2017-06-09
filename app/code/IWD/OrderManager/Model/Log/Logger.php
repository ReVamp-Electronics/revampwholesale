<?php

namespace IWD\OrderManager\Model\Log;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Backend\Model\Auth\Session;
use IWD\OrderManager\Model\LogFactory;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Class Logger
 * @package IWD\OrderManager\Model\Log
 */
class Logger
{
    const BR = '<br/>';

    const FORMAT_STRING = 'string';
    const FORMAT_PRICE = 'price';
    const FORMAT_PERCENT = 'percent';

    /**
     * @var Logger|null
     */
    private static $instance = null;

    /**
     * @var string[]
     */
    private $logs;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var PsrLogger
     */
    protected $psrLogger;

    /**
     * Logger constructor.
     * @param PricingHelper $pricingHelper
     * @param Session $authSession
     * @param LogFactory $logFactory
     * @param PsrLogger $psrLogger
     */
    public function __construct(
        PricingHelper $pricingHelper,
        Session $authSession,
        LogFactory $logFactory,
        PsrLogger $psrLogger
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->authSession = $authSession;
        $this->logFactory = $logFactory;
        $this->psrLogger = $psrLogger;
    }

    /**
     * @param string $message
     * @param null|int|float $old
     * @param string|int|float $new
     * @param null|string $level
     * @param string $format
     * @return void
     */
    public function addChange(
        $message,
        $old,
        $new,
        $level = null,
        $format = self::FORMAT_STRING
    ) {
        if (empty($message) || empty($new)) {
            return;
        }

        if (empty($old)) {
            $new = $this->formatValue($new, $format);
            $log = __('%1 was changed to "%2"', __($message), $new);
            $this->addMessage($log, $level);
            return;
        }

        if ($old != $new) {
            $old = $this->formatValue($old, $format);
            $new = $this->formatValue($new, $format);
            $log = __(
                '%1 was changed from "%2" to "%3"',
                __($message),
                $old,
                $new
            );
            $this->addMessage($log, $level);
            return;
        }
    }

    /**
     * @param string $comment
     * @param int $orderId
     * @param int $orderIncrementId
     * @return void
     */
    public function addLogIntoLogTable($comment, $orderId, $orderIncrementId)
    {
        try {
            $adminUser = $this->getCurrentUser();

            $log = $this->logFactory->create();
            $log->setDescription($comment)
                ->setOrderId($orderId)
                ->setOrderIncrementId($orderIncrementId)
                ->setAdminId($adminUser->getId())
                ->setAdminName($adminUser->getName())
                ->save();
        } catch (\Exception $e) {
            $this->psrLogger->expects($e);
        }
    }

    /**
     * @param int|float $value
     * @param string $format
     * @return float|string
     */
    private function formatValue($value, $format)
    {
        switch ($format) {
            case self::FORMAT_PRICE:
                return $this->pricingHelper->currency($value, true, false);
            case self::FORMAT_PERCENT:
                return number_format($value, 2) . '%';
            default:
                return $value;
        }
    }

    /**
     * @return Logger
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = ObjectManager::getInstance()->get(get_called_class());
        }
        return self::$instance;
    }

    /**
     * @return string[]
     */
    public function getLogs()
    {
        $logs = $this->logs;
        if (!is_array($logs)) {
             $logs = is_string($logs) ? [$logs] : [];
        }

        return $logs;
    }

    /**
     * @param $order
     * @return void
     */
    public function saveLogs($order)
    {
        $this->saveLogsAsOrderComments($order);
        $this->saveLogsInLogTable($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function saveLogsAsOrderComments($order)
    {
        $comment = $this->getFormattedLogString();
        if (empty($comment)) {
            return;
        }

        try {
            $status = false;
            $notify = false;
            $visible = false;

            $history = $order->addStatusHistoryComment($comment, $status);
            $history->setIsVisibleOnFront($visible);
            $history->setIsCustomerNotified($notify);
            $history->save();
        } catch (\Exception $e) {
            $this->psrLogger->expects($e);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function saveLogsInLogTable($order)
    {
        if (count($this->getLogs()) == 0) {
            return;
        }

        $comment = $this->getFormattedLogString();
        $this->addLogIntoLogTable(
            $comment,
            $order->getId(),
            $order->getIncrementId()
        );
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    private function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * @return string
     */
    public function getFormattedLogString()
    {
        $logs = $this->getLogs();
        $logString = '';

        foreach ($logs as $id => $log) {
            if (is_array($log)) {
                if (!empty($log)) {
                    $itemLogString = $log[$id] . ':' . self::BR . ' --- ';
                    unset($log[$id]);
                    $itemLogString .= implode(self::BR . ' --- ', $log) . self::BR;
                    $logString .= empty($log) ? '' : $itemLogString;
                }
            } else {
                $logString .= $log . self::BR;
            }
        }

        return $logString;
    }

    /**
     * @param string $message
     * @param string|null $level
     * @return void
     */
    public function addMessage($message, $level = null)
    {
        if (!empty($message)) {
            if (empty($level)) {
                $this->logs[] = $message;
            } else {
                $this->logs[$level][] = $message;
            }
        }
    }

    /**
     * @param string $level
     * @param string $message
     * @return void
     */
    public function addMessageForLevel($level, $message)
    {
        $this->logs[$level][$level] = $message;
    }
}
