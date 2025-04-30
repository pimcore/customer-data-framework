<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use CustomerManagementFrameworkBundle\Installer;
use Pimcore\Model\DataObject\Customer;
use Pimcore\Tests\Support\Helper\AbstractDefinitionHelper;
use Pimcore\Tests\Support\Helper\ClassManager;
use Pimcore\Tests\Support\Helper\Pimcore;
use Pimcore\Tests\Support\Util\Autoloader;

class Model extends AbstractDefinitionHelper
{
    public function _beforeSuite($settings = []): void
    {
        /** @var Pimcore $pimcoreModule */
        $pimcoreModule = $this->getModule('\\' . Pimcore::class);

        $this->debug('[CMF] Running cmf installer');

        // install ecommerce framework
        $installer = $pimcoreModule->getContainer()->get(Installer::class);
        $installer->install();

        $this->initializeDefinitions();
        Autoloader::load(Customer::class);
    }

    public function initializeDefinitions(): void
    {
        $cm = $this->getModule('\\' . ClassManager::class);
        $cm->setupClass('Customer', __DIR__ . '/../../../install/class_source/optional/class_Customer_export.json');
    }
}
