<?php

namespace IWD\OrderManager\Model\Order;

use IWD\OrderManager\Model\Log\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class History
 * @package IWD\OrderManager\Model\Order
 */
class History extends \Magento\Sales\Model\Order\Status\History
{
    /**
     * @return void
     */
    public function deleteComment()
    {
        Logger::getInstance()->addLogIntoLogTable(
            __('Order comment has been deleted.') . Logger::BR .
            __('Comment: ') . Logger::BR . $this->getComment(),
            $this->getOrder()->getId(),
            $this->getOrder()->getIncrementId()
        );

        $this->delete();
    }

    /**
     * @param string $commentText
     * @param null $visible
     * @return void
     */
    public function updateComment($commentText, $visible = null)
    {
        Logger::getInstance()->addLogIntoLogTable(
            __('Order comment has been updated.') . Logger::BR .
            '<i>' . __('Old comment: ') . '</i>' . Logger::BR .
            $this->getComment() . Logger::BR .
            '<i>' .__('New comment: ') . '</i>' . Logger::BR . $commentText,
            $this->getOrder()->getId(),
            $this->getOrder()->getIncrementId()
        );

        $this->setComment($commentText);

        if ($visible !== null) {
            $this->setIsVisibleOnFront($visible);
        }

        $this->save();
    }

    /**
     * @param int $id
     * @return $this
     * @throws \Exception
     */
    public function getCommentById($id)
    {
        $this->loadById($id);
        return $this;
    }

    /**
     * @param int $id
     * @return void
     * @throws \Exception
     */
    protected function loadById($id)
    {
        $this->load($id);
        $entityId = $this->getEntityId();
        if (empty($entityId)) {
            throw new LocalizedException(__('Can not load comment.'));
        }
    }
}
