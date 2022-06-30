<?php

namespace VexShipping\Glovo\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;
    protected $salesSetupFactory;
    private $customerSetupFactory;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $installer = $setup;
 
            $installer->startSetup();
     
            $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $installer]);
     
            $salesSetup->addAttribute(Order::ENTITY, 'idglovo', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'=> 255,
                'visible' => false,
                'nullable' => true
            ]);

     
     
            $installer->endSetup();
            

        }




        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $obj = \Magento\Framework\App\ObjectManager::getInstance();
            $connection = $obj->get('Magento\Framework\App\ResourceConnection')->getConnection();
            $tablequote = $connection->getTableName('quote_address');
            $tablesales = $connection->getTableName('sales_order_address');

            $setup->getConnection()->addColumn(
                $setup->getTable($tablequote),
                'coordenadas',
                [
                    'type' => 'text',
                    'length' => 255,
                    'comment' => 'Coordenadas'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable($tablesales),
                'coordenadas',
                [
                    'type' => 'text',
                    'length' => 255,
                    'comment' => 'Coordenadas'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable($tablequote),
                'tiempo',
                [
                    'type' => 'text',
                    'length' => 255,
                    'comment' => 'Tiempo'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable($tablesales),
                'tiempo',
                [
                    'type' => 'text',
                    'length' => 255,
                    'comment' => 'Tiempo'
                ]
            );

     
     
            $setup->endSetup();
            

        }


        if (version_compare($context->getVersion(), '1.0.3') < 0) {


            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'glovo_width');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'glovo_width',
                [
                    'type' => 'decimal',
                    'label' => 'Width (cm)',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Type::TYPE_SIMPLE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );


            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'glovo_height');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'glovo_height',
                [
                    'type' => 'decimal',
                    'label' => 'Height (cm)',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Type::TYPE_SIMPLE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );


            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'glovo_long');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'glovo_long',
                [
                    'type' => 'decimal',
                    'label' => 'Long (cm)',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Type::TYPE_SIMPLE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );


            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'glovo_preparation_time');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'glovo_preparation_time',
                [
                    'type' => 'int',
                    'label' => 'Preparation Time (hours)',
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => Type::TYPE_SIMPLE,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true
                ]
            );



        }


        if (version_compare($context->getVersion(), '1.0.5') < 0) {

            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerSetup->addAttribute('customer_address', 'coordenadas', [
                'label' => 'Coordenadas',
                'input' => 'text',
                'type' => 'varchar',
                'source' => '',
                'required' => false,
                'position' => 333,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'backend' => '',
                'comment' => 'Coordenada'
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'coordenadas')
                ->addData(['used_in_forms' => [
                    'customer_register_address',
                    'customer_address_edit',
                    'adminhtml_customer_address',
                ]]);
            $attribute->save();

            




            $customerSetup->addAttribute('customer_address', 'tiempo', [
                'label' => 'Tiempo',
                'input' => 'text',
                'type' => 'varchar',
                'source' => '',
                'required' => false,
                'position' => 333,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
                'backend' => '',
                'comment' => 'Tiempo'
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'tiempo')
                ->addData(['used_in_forms' => [
                    'customer_register_address',
                    'customer_address_edit',
                    'adminhtml_customer_address',
                ]]);
            $attribute->save();

        }






    }
}