<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Aheadworks\Helpdesk\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * Bookmark helper
     *
     * @var \Aheadworks\Helpdesk\Helper\Bookmark
     */
    protected $definedBookmarkHelper;

    /**
     * User collection
     *
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    protected $userCollection;

    /**
     * @param \Aheadworks\Helpdesk\Helper\Bookmark $bookmarkHelper
     * @param \Magento\User\Model\ResourceModel\User\Collection $userCollection
     */
    public function __construct(
        \Aheadworks\Helpdesk\Helper\Bookmark $bookmarkHelper,
        \Magento\User\Model\ResourceModel\User\Collection $userCollection
    ) {
        $this->definedBookmarkHelper = $bookmarkHelper;
        $this->userCollection = $userCollection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'aw_helpdesk_department'
         */
        $departmentTable = $installer->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_department')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'is_enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Enabled'
        )->addColumn(
            'is_visible',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Visible On StoreFront'
        )->addColumn(
            'is_default',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Default Department'
        );
        $installer->getConnection()->createTable($departmentTable);

        /**
         * Create table 'aw_helpdesk_department_website'
         */
        $departmentWebsiteTable = $installer->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_department_website')
        )->addColumn(
            'department_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website Id'
        )->addForeignKey(
            $installer->getFkName('aw_helpdesk_department_website', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('aw_helpdesk_department_website', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $installer->getTable('aw_helpdesk_department'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($departmentWebsiteTable);

        /**
         * Create table 'aw_helpdesk_department_label'
         */
        $departmentLabelTable = $installer->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_department_label')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'department_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store Id'
        )->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Department Label'
        )->addForeignKey(
            $installer->getFkName('aw_helpdesk_department_label', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $installer->getTable('aw_helpdesk_department'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($departmentLabelTable);

        /**
         * Create table 'aw_helpdesk_department_gateway'
         */
        $gatewayTable = $installer->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_department_gateway')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'department_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'default_store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Default Store Id'
        )->addColumn(
            'is_enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'protocol',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Protocol'
        )->addColumn(
            'host',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Gateway Host'
        )->addColumn(
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Gateway Email'
        )->addColumn(
            'login',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Login'
        )->addColumn(
            'password',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Password'
        )->addColumn(
            'secure_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Secure Type'
        )->addColumn(
            'port',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'Port'
        )->addColumn(
            'is_delete_parsed',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Delete parsed emails'
        )->addForeignKey(
            $installer->getFkName('aw_helpdesk_department_gateway', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $installer->getTable('aw_helpdesk_department'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($gatewayTable);

        /**
         * Create table 'aw_helpdesk_department_permission'
         */
        $departmentPermissionTable = $setup->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_department_permission')
        )->addColumn(
            'department_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Department Id'
        )->addColumn(
            'role_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Role Id'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Type'
        )->addForeignKey(
            $installer->getFkName('aw_helpdesk_department_permission', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $installer->getTable('aw_helpdesk_department'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($departmentPermissionTable);

        /**
         * Create table 'aw_helpdesk_ticket'
         */
        $ticketTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_ticket'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'uid',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['nullable' => false,],
                'Ticket Uid'
            )->addColumn(
                'department_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Department Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false,],
                'Created At'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true]
            )->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Customer Email'
            )->addColumn(
                'customer_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Customer Name'
            )->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Subject'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Priority'
            )->addColumn(
                'agent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Agent ID'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Order ID'
            )->addColumn(
                'cc_recipients',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'CC Recipients'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            )->addForeignKey(
                $installer->getFkName('aw_helpdesk_ticket', 'department_id', 'aw_helpdesk_department', 'id'),
                'department_id',
                $installer->getTable('aw_helpdesk_department'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_RESTRICT
            );

        $installer->getConnection()->createTable($ticketTable);

        /**
         * Create table 'aw_helpdesk_ticket_message'
         */
        $messageTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_ticket_message'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'ticket_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Ticket ID'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Content'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Type'
            )->addColumn(
                'author_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Author Name'
            )->addColumn(
                'author_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Author Email'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addForeignKey(
                $installer->getFkName('aw_helpdesk_ticket_message', 'ticket_id', 'aw_helpdesk_ticket', 'id'),
                'ticket_id',
                $installer->getTable('aw_helpdesk_ticket'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($messageTable);

        /**
         * Create table 'aw_helpdesk_attachment'
         */
        $attachmentTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_attachment'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'message_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Message ID'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                '10M',
                ['nullable' => false],
                'Content'
            )->addForeignKey(
                $installer->getFkName('aw_helpdesk_attachment', 'message_id', 'aw_helpdesk_ticket_message', 'id'),
                'message_id',
                $installer->getTable('aw_helpdesk_ticket_message'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($attachmentTable);

        /**
         * Create table 'aw_helpdesk_automation'
         */
        $automationTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_automation'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Priority'
            )->addColumn(
                'event',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Event'
            )->addColumn(
                'conditions',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Serialized Conditions'
            )->addColumn(
                'actions',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Serialized Action'
            );
        $installer->getConnection()->createTable($automationTable);

        /**
         * Create table 'aw_helpdesk_automation_cron_schedule'
         */
        $cronTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_automation_cron_schedule'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'automation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Automation ID'
            )->addColumn(
                'ticket_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Ticket ID'
            )->addColumn(
                'action_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Action Type'
            )->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Action'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Cron Job Status'
            );
        $installer->getConnection()->createTable($cronTable);

        /**
         * Create flat table 'aw_helpdesk_ticket_grid_flat' for grid
         */
        $ticketFlatTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_ticket_grid_flat'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'ticket_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Ticket ID'
            )->addColumn(
                'agent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Agent ID'
            )->addColumn(
                'agent_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Agent Name'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Order Increment ID'
            )->addColumn(
                'last_reply_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Last Reply Type'
            )->addColumn(
                'last_reply_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Last Reply By'
            )->addColumn(
                'last_reply_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false,],
                'Last Reply Date'
            )->addColumn(
                'customer_messages',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Customer Messages Count'
            )->addColumn(
                'agent_messages',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Agent Messages Count'
            )->addColumn(
                'first_message_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Ticket First Message'
            )->addForeignKey(
                $installer->getFkName('aw_helpdesk_ticket_grid_flat', 'ticket_id', 'aw_helpdesk_ticket', 'id'),
                'ticket_id',
                $installer->getTable('aw_helpdesk_ticket'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($ticketFlatTable);

        /**
         * Create table 'aw_helpdesk_gateway_mail'
         */
        $mailTable = $installer->getConnection()->newTable($installer->getTable('aw_helpdesk_gateway_mail'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'ticket_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['nullable' => false,],
                'Ticket Id'
            )->addColumn(
                'uid',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Ticket UID'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Website ID'
            )->addColumn(
                'from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Email From'
            )->addColumn(
                'to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Email To'
            )->addColumn(
                'gateway_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Gateway Email'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false,],
                'Created at'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Mail Status'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Mail Type'
            )->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Subject'
            )->addColumn(
                'body',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Body'
            )->addColumn(
                'headers',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Headers'
            )->addColumn(
                'content_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Content Type'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            );
        $installer->getConnection()->createTable($mailTable);

        /**
         * Create table 'aw_helpdesk_gateway_mail_attachment'
         */
        $mailAttachmentTable = $installer->getConnection()->newTable(
            $installer->getTable('aw_helpdesk_gateway_mail_attachment')
        )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'mailbox_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Mailbox ID'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Name'
            )->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                '10M',
                ['nullable' => false],
                'Content'
            )->addForeignKey(
                $installer->getFkName(
                    'aw_helpdesk_gateway_mail_attachment',
                    'mailbox_id',
                    'aw_helpdesk_gateway_mail',
                    'id'
                ),
                'mailbox_id',
                $installer->getTable('aw_helpdesk_gateway_mail'),
                'id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($mailAttachmentTable);

        /**
         * Create table 'aw_helpdesk_config'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_helpdesk_config'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Parameter Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Parameter Name'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Value'
            )
            ->setComment('AW Helpdesk Config');
        $installer->getConnection()->createTable($table);

        $installer->getConnection()
            ->insert($installer->getTable('aw_helpdesk_config'),
                [
                    'name' => \Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_TICKET,
                    'value' => time()
                ]
            );
        $installer->getConnection()
            ->insert($installer->getTable('aw_helpdesk_config'),
                [
                    'name' => \Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_MAIL,
                    'value' => time()
                ]
            );
        $installer->getConnection()
            ->insert($installer->getTable('aw_helpdesk_config'),
                [
                    'name' => \Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_CREATE_AUTOMATION,
                    'value' => time()
                ]
            );
        $installer->getConnection()
            ->insert($installer->getTable('aw_helpdesk_config'),
                [
                    'name' => \Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_RUN_AUTOMATION,
                    'value' => time()
                ]
            );
        $installer->getConnection()
            ->insert($installer->getTable('aw_helpdesk_config'),
                [
                    'name' => \Aheadworks\Helpdesk\Model\Config::LAST_EXEC_TIME_UPDATE_AUTOMATION,
                    'value' => time()
                ]
            );

        $this->installBookmarks();
        $installer->endSetup();
    }

    /**
     * Install bookmarks
     * @return void
     */
    protected function installBookmarks()
    {
        foreach ($this->userCollection as $user) {
            $this->definedBookmarkHelper->proceedAll($user);
        }
    }
}
