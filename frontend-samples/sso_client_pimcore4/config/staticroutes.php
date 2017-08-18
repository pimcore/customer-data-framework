<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

return [
    1 => [
        'id' => 1,
        'name' => 'auth',
        'pattern' => '#^/auth/([\\w-]+)/?#',
        'reverse' => '/auth/%action',
        'module' => null,
        'controller' => 'auth',
        'action' => '%action',
        'variables' => 'action',
        'defaults' => null,
        'siteId' => null,
        'priority' => 0
    ]
];
