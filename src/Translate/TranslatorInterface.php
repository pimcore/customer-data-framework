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

namespace CustomerManagementFrameworkBundle\Translate;

interface TranslatorInterface
{
    /**
     * Translates a message. Optional parameters are passed to sprintf().
     *
     * @param string $messageId
     * @param mixed $parameters
     *
     * @return string
     */
    public function translate($messageId, $parameters = []);
}
