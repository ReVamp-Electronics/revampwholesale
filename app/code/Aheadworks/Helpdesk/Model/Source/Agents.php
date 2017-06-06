<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Agents
 * @package Aheadworks\Helpdesk\Model\Source
 */
class Agents implements OptionSourceInterface
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    private $userCollection;

    /**
     * Stores constructor.
     * @param \Magento\User\Model\ResourceModel\User\Collection $userCollection
     * @internal param Options $storeOptions
     */
    public function __construct(\Magento\User\Model\ResourceModel\User\Collection $userCollection)
    {
        $this->userCollection = $userCollection;
    }

    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        $users = [];
        $users[] = [
            'value' => '',
            'label' => __('Unassigned'),
        ];
        /** @var \Magento\User\Model\User $userModel */
        foreach ($this->userCollection as $userModel) {
            $users[] = [
                'value' => $userModel->getId(),
                'label' => $userModel->getFirstName() . ' ' . $userModel->getLastName(),
            ];
        }

        return $users;
    }
}
