<?php

namespace MW\RewardPoints\Controller\Rewardpoints;

class ApplyCron extends \MW\RewardPoints\Controller\Rewardpoints
{
    public function execute()
    {
        $this->_objectManager->get('MW\RewardPoints\Cron\ApplyRulesCronEvery')->execute();
    }
}
