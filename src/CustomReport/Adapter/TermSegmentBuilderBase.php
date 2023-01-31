<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomReport\Adapter;

use Pimcore\Bundle\CustomReportsBundle\Tool\Adapter\Sql;
use Pimcore\Version;

//@TODO remove BC layer when dropping support for Pimcore 10
if (Version::getMajorVersion() >= 11) {
    class_exists(Sql::class);

    class TermSegmentBuilderBase extends Sql
    {
    }
} else {
    class TermSegmentBuilderBase extends \Pimcore\Model\Tool\CustomReport\Adapter\Sql
    {
    }
}
