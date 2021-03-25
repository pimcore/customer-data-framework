<?php
namespace CustomerManagementFrameworkBundle\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use CustomerManagementFrameworkBundle\Installer;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Helper\AbstractDefinitionHelper;
use Pimcore\Tests\Helper\Pimcore;
use Pimcore\Tests\Util\Autoloader;

class Model extends AbstractDefinitionHelper
{

    public function _beforeSuite($settings = [])
    {
        /** @var Pimcore $pimcoreModule */
        $pimcoreModule = $this->getModule('\\' . Pimcore::class);

        $this->debug('[CMF] Running cmf installer');


        //create migrations table in order to allow installation - needed for SettingsStoreAware Installer
        \Pimcore\Db::get()->exec('
create table migration_versions
(
	version varchar(1024) not null
		primary key,
	executed_at datetime null,
	execution_time int null
)
collate=utf8_unicode_ci;

');

        // install ecommerce framework
        $installer = $pimcoreModule->getContainer()->get(Installer::class);
        $installer->install();

        $this->initializeDefinitions();
        Autoloader::load(Customer::class);
    }

    public function initializeDefinitions()
    {
        $cm = $this->getClassManager();
        $cm->setupClass('Customer', __DIR__ . '/../../../install/class_source/optional/class_Customer_export.json');
    }
}
