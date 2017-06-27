<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace EH\CustomerApprove\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use EH\CustomerApprove\Helper\Data as CustomerApproveHelper;

/**
 * Class DisapproveCommand
 */
class DisapproveCommand extends Command
{
    /**
     * ID argument
     */
    const ID_ARGUMENT = 'customer_id';
	
	protected $customerRepository;
	protected $customerApproveHelper;
	protected $objectManager;
	
	public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerApproveHelper $customerApproveHelper,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct();
        $this->customerRepository = $customerRepository;
		$this->customerApproveHelper = $customerApproveHelper;
		$this->objectManager = $objectManager;
    }
	
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('customer:disapprove')
            ->setDescription('Disapprove Customer(s) [ExtensionHut]')
            ->setDefinition([
                new InputArgument(
                    self::ID_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Comma-separated IDs to disapprove the customers'
                ),

            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$areaCode = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        
        $data = $input->getArgument(self::ID_ARGUMENT);
        if (is_null($data)) {
			throw new \InvalidArgumentException('Argument ' . self::ID_ARGUMENT . ' is missing. It can be comma separated.');
        }
        $ids = explode(',',$data);
        foreach($ids as $id) {
			$customerData = $this->customerRepository->getById($id);
			if (!$customerData->getId()) {
				$output->writeln('<error>Customer ID: '.$id.' - no longer exist or invalid customer id.</error>');
			} else if ($customerData->getCustomAttribute('eh_is_approved')->getValue() == 0) {
				$output->writeln('<info>Customer ID: '.$id.' - is already disapprove.</info>');
			} else {
				// disapprove customer
				$isApproved = $customerData->setCustomAttribute('eh_is_approved', 0);
				$this->customerRepository->save($customerData);
				$output->writeln('<info>Customer ID: '.$id.' - has been disapprove.</info>');
			}
		}
    }
}
