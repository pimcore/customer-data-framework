<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataTransformer;

interface DataTransformerInterface
{
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return mixed
     */
    public function transform($data, $options = []);
}
