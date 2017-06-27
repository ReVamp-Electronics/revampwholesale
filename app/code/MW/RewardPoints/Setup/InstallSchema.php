<?php

namespace MW\RewardPoints\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
	/**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$setup->startSetup();

        // Create mw_reward_point_order table
        $tableName = $setup->getTable('mw_reward_point_order');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'order_id',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false, 'primary' => true],
					'Order ID'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false],
					'Reward Point'
				)
				->addColumn(
					'rewardpoint_sell_product',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '0'],
					'Reward Point Sell Product'
				)
				->addColumn(
					'earn_rewardpoint',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '0'],
					'Earn Reward Point'
				)
				->addColumn(
					'money',
					Table::TYPE_FLOAT,
					null,
					['unsigned' => true, 'nullable' => false],
					'Money'
				)
				->addColumn(
					'reward_point_money_rate',
					Table::TYPE_TEXT,
					null,
					['nullable' => false],
					'Reward Point Money Rate'
				)
				->setComment('Reward Point Product Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_point_history table
        $tableName = $setup->getTable('mw_reward_point_history');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'history_id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'History ID'
				)
				->addColumn(
					'customer_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'unsigned' => true, 'default' => '0'],
					'Customer ID'
				)
				->addColumn(
					'type_of_transaction',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false],
					'Type Of Transaction'
				)
				->addColumn(
					'amount',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false],
					'Amount'
				)
				->addColumn(
					'balance',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false],
					'Balance'
				)
				->addColumn(
					'transaction_detail',
					Table::TYPE_TEXT,
					null,
					['nullable' => true, 'default' => ''],
					'Transaction Detail'
				)
				->addColumn(
					'transaction_time',
					Table::TYPE_DATETIME,
					null,
					['nullable' => true],
					'Transaction Time'
				)
				->addColumn(
					'history_order_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '0'],
					'History Order ID'
				)
				->addColumn(
					'expired_day',
					Table::TYPE_INTEGER,
					null,
					['default' => '0'],
					'Expired Day'
				)
				->addColumn(
					'expired_time',
					Table::TYPE_DATETIME,
					null,
					['nullable' => true],
					'Expired Time'
				)
				->addColumn(
					'point_remaining',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '0'],
					'Point Remaining'
				)
				->addColumn(
					'check_time',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '1'],
					'Check Time'
				)
				->addColumn(
					'status',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false],
					'Status'
				)
				->addColumn(
					'status_check',
					Table::TYPE_INTEGER,
					null,
					['nullable' => true, 'default' => '0'],
					'Status Check'
				)
				->setComment('Reward Point History Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_point_customer table
        $tableName = $setup->getTable('mw_reward_point_customer');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'customer_id',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false, 'primary' => true],
					'Customer ID'
				)
				->addColumn(
					'mw_reward_point',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'unsigned' => true, 'default' => '0'],
					'Reward Point'
				)
				->addColumn(
					'mw_friend_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'unsigned' => true, 'default' => '0'],
					'Friend ID'
				)
				->addColumn(
					'last_checkout',
					Table::TYPE_DATETIME,
					null,
					['nullable' => false],
					'Last Checkout Time'
				)
				->addColumn(
					'subscribed_balance_update',
					Table::TYPE_INTEGER,
					null,
					['default' => '1'],
					'Subscribed Balance Update'
				)
				->addColumn(
					'subscribed_point_expiration',
					Table::TYPE_INTEGER,
					null,
					['default' => '1'],
					'Subscribed Point Expiration'
				)
				->setComment('Reward Point Customer Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_product_point table
        $tableName = $setup->getTable('mw_reward_product_point');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'ID'
				)
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Rule ID'
				)
				->addColumn(
					'product_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Product ID'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Point'
				)
				->setComment('Reward Point Product Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_catalog_rules table
		$tableName = $setup->getTable('mw_reward_catalog_rules');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'Rule ID'
				)
				->addColumn(
					'name',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Name'
				)
				->addColumn(
					'description',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Description'
				)
				->addColumn(
					'conditions_serialized',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Conditions Serialized'
				)
				->addColumn(
					'store_view',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => '0'],
					'Store View'
				)
				->addColumn(
					'customer_group_ids',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Customer Group Ids'
				)
				->addColumn(
					'start_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Start Date'
				)
				->addColumn(
					'end_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'End Date'
				)
				->addColumn(
					'simple_action',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Simple Action'
				)
				->addColumn(
					'reward_step',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Step'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Point'
				)
				->addColumn(
					'rule_position',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Rule Position'
				)
				->addColumn(
					'stop_rules_processing',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Stop Rules Processing'
				)
				->addColumn(
					'status',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Status'
				)
				->setComment('Reward Point Catalog Rule Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_cart_rules table
		$tableName = $setup->getTable('mw_reward_cart_rules');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'Rule ID'
				)
				->addColumn(
					'name',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Name'
				)
				->addColumn(
					'description',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Description'
				)
				->addColumn(
					'promotion_message',
					Table::TYPE_TEXT,
					null,
					['nullable' => true, 'default' => ''],
					'Promotion Message'
				)
				->addColumn(
					'promotion_image',
					Table::TYPE_TEXT,
					null,
					['nullable' => true, 'default' => ''],
					'Promotion Image'
				)
				->addColumn(
					'conditions_serialized',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Conditions Serialized'
				)
				->addColumn(
					'actions_serialized',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Actions Serialized'
				)
				->addColumn(
					'store_view',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => '0'],
					'Store View'
				)
				->addColumn(
					'customer_group_ids',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Customer Group Ids'
				)
				->addColumn(
					'start_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Start Date'
				)
				->addColumn(
					'end_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'End Date'
				)
				->addColumn(
					'simple_action',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Simple Action'
				)
				->addColumn(
					'reward_step',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Step'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Point'
				)
				->addColumn(
					'rule_position',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Rule Position'
				)
				->addColumn(
					'stop_rules_processing',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Stop Rules Processing'
				)
				->addColumn(
					'status',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Status'
				)
				->setComment('Reward Point Catalog Rule Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_active_rules table
		$tableName = $setup->getTable('mw_reward_active_rules');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'Rule ID'
				)
				->addColumn(
					'rule_name',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Rule Name'
				)
				->addColumn(
					'type_of_transaction',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Type Of Transaction'
				)
				->addColumn(
					'store_view',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => '0'],
					'Store View'
				)
				->addColumn(
					'customer_group_ids',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Customer Group Ids'
				)
				->addColumn(
					'default_expired',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '1'],
					'Default Expired'
				)
				->addColumn(
					'expired_day',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Expired Day'
				)
				->addColumn(
					'date_event',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Date Event'
				)
				->addColumn(
					'comment',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Comment'
				)
				->addColumn(
					'coupon_code',
					Table::TYPE_TEXT,
					null,
					['nullable' => true, 'default' => ''],
					'Coupon Code'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => '0'],
					'Reward Point'
				)
				->addColumn(
					'status',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Status'
				)
				->setComment('Reward Point Active Rules Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_spend_cart_rules table
		$tableName = $setup->getTable('mw_reward_spend_cart_rules');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'Rule ID'
				)
				->addColumn(
					'name',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Name'
				)
				->addColumn(
					'description',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Description'
				)
				->addColumn(
					'conditions_serialized',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Conditions Serialized'
				)
				->addColumn(
					'actions_serialized',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Actions Serialized'
				)
				->addColumn(
					'store_view',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => '0'],
					'Store View'
				)
				->addColumn(
					'customer_group_ids',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Customer Group Ids'
				)
				->addColumn(
					'start_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'Start Date'
				)
				->addColumn(
					'end_date',
					Table::TYPE_TEXT,
					null,
					['nullable' => false, 'default' => ''],
					'End Date'
				)
				->addColumn(
					'simple_action',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Simple Action'
				)
				->addColumn(
					'reward_step',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Step'
				)
				->addColumn(
					'reward_point',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Reward Point'
				)
				->addColumn(
					'rule_position',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Rule Position'
				)
				->addColumn(
					'stop_rules_processing',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Stop Rules Processing'
				)
				->addColumn(
					'status',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Status'
				)
				->setComment('Reward Point Spent Cart Rule Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Create mw_reward_point_sell_point table
		$tableName = $setup->getTable('mw_reward_point_sell_point');
		// Check if the table already exists
		if ($setup->getConnection()->isTableExists($tableName) != true) {
			$table = $setup->getConnection()
				->newTable($tableName)
				->addColumn(
					'id',
					Table::TYPE_INTEGER,
					null,
					['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
					'ID'
				)
				->addColumn(
					'product_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Product ID'
				)
				->addColumn(
					'option_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Option ID'
				)
				->addColumn(
					'option_type_id',
					Table::TYPE_INTEGER,
					null,
					['nullable' => false, 'default' => '0'],
					'Option Type ID'
				)
				->addColumn(
					'sell_point',
					Table::TYPE_DECIMAL,
					'11,4',
					['nullable' => false, 'default' => '0.0000'],
					'Sell Point'
				)
				->addColumn(
					'earn_point',
					Table::TYPE_DECIMAL,
					'11,4',
					['nullable' => false, 'default' => '0.0000'],
					'Earn Point'
				)
				->addColumn(
					'type_id',
					Table::TYPE_TEXT,
					'255',
					['nullable' => false, 'default' => 'custom_option'],
					'Type ID'
				)
				->setComment('Reward Point Product Sell Point Table')
				->setOption('type', 'InnoDB')
				->setOption('charset', 'utf8');
			$setup->getConnection()->createTable($table);
		}

		// Add reward point information on quote table
		$connection = $setup->getConnection();
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'unsigned' => true,
				'nullable' => false,
				'default' => '0',
				'comment' => 'Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint_discount',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint_discount_show',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount Show'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'earn_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Earn Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'earn_rewardpoint_cart',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Earn Reward Point Cart'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'spend_rewardpoint_cart',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Spend Reward Point Cart'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint_sell_product',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Sell Product'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint_detail',
			[
				'type' => Table::TYPE_TEXT,
				'nullable' => true,
				'default' => '',
				'comment' => 'Reward Point Detail'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mw_rewardpoint_rule_message',
			[
				'type' => Table::TYPE_TEXT,
				'nullable' => true,
				'default' => '',
				'comment' => 'Reward Point Rule Message'
			]
		);

		// Add reward point information on quote_address table
		$connection->addColumn(
			$setup->getTable('quote_address'),
			'mw_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote_address'),
			'mw_rewardpoint_discount',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote_address'),
			'mw_rewardpoint_discount_show',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount Show'
			]
		);

		// Add reward point information on sales_order table
		$connection->addColumn(
			$setup->getTable('sales_order'),
			'mw_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_order'),
			'mw_rewardpoint_discount',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_order'),
			'mw_rewardpoint_discount_show',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount Show'
			]
		);

		// Add reward point information on sales_invoice table
		$connection->addColumn(
			$setup->getTable('sales_invoice'),
			'mw_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_invoice'),
			'mw_rewardpoint_discount',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_invoice'),
			'mw_rewardpoint_discount_show',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount Show'
			]
		);

		// Add reward point information on sales_creditmemo table
		$connection->addColumn(
			$setup->getTable('sales_creditmemo'),
			'mw_rewardpoint',
			[
				'type' => Table::TYPE_INTEGER,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Reward Point'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_creditmemo'),
			'mw_rewardpoint_discount',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_creditmemo'),
			'mw_rewardpoint_discount_show',
			[
				'type' => Table::TYPE_DECIMAL,
				'nullable' => true,
				'length' => '12,4',
				'default' => '0.0000',
				'comment' => 'Reward Point Discount Show'
			]
		);

		$setup->endSetup();
	}
}
