<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace AppBundle;

use HWI\Bundle\OAuthBundle\HWIOAuthBundle;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle implements DependentBundleInterface
{
    public static function registerDependentBundles(BundleCollection $collection)
    {
        // activate bundle for SSO oauth login/register functionality
        $collection->addBundle(HWIOAuthBundle::class);
    }
}
