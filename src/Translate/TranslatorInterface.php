<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Translate;

interface TranslatorInterface
{
    /**
     * Translates a message. Optional parameters are passed to sprintf().
     *
     * @param string $messageId
     * @param array|mixed $parameters
     *
     * @return string
     */
    public function translate($messageId, $parameters = []);
}
