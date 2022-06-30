<?php
namespace VexShipping\Glovo\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements  UpgradeSchemaInterface{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.4') < 0) {

            $table  = $setup->getConnection()
                    ->newTable($setup->getTable('vexsoluciones_glovo'))
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Id'
                    )
                    ->addColumn(
                        'id_glovo',
                        Table::TYPE_INTEGER,
                        null,
                        ['default' => null],
                        'id_glovo'
                    )
                    ->addColumn(
                        'id_order',
                        Table::TYPE_INTEGER,
                        null,
                        ['default' => null],
                        'id_order'
                    )
                    ->addColumn(
                        'increment_order',
                        Table::TYPE_TEXT,
                        null,
                        ['default' => null],
                        'increment_order'
                    )
                    ->addColumn(
                        'status_order',
                        Table::TYPE_TEXT,
                        null,
                        ['default' => null],
                        'status_order'
                    )
                    ->addColumn(
                        'glovo_log',
                        Table::TYPE_TEXT,
                        null,
                        ['default' => null],
                        'glovo_log'
                    )


                    ->addColumn(
                        'status',
                        Table::TYPE_INTEGER,
                        null,
                        ['default' => null],
                        'status'
                    )->addColumn(
                        'fecha',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['default' => null],
                        'fecha'
                    );
            
            $setup->getConnection()->createTable($table);


        }


        if (version_compare($context->getVersion(), '1.0.6') < 0) {

            $setup->getConnection()->addColumn(
                $setup->getTable('vexsoluciones_glovo'),
                'glovo_log_cancel',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => "",
                    'comment' => 'glovo_log_cancel'
                ]
            );

        }

        $setup->endSetup();

    }

}