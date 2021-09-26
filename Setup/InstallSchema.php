<?php
/*
 * @Author      Hemant Singh
 * @Developer   Hemant Singh
 * @Module      Wishusucess_Newsletter
 * @copyright   Copyright (c) Wishusucess (http://www.wishusucess.com/)
 */
namespace Wishusucess\Newsletter\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {
  public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
    $setup->startSetup();
    $table = $setup->getTable('newsletter_subscriber');

    $setup->getConnection()->addColumn(
      $table, 'gender', [
        'type' => Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Gender'
      ]
    );

    $setup->endSetup();
  }
}
