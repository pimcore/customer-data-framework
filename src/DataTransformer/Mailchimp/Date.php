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

namespace CustomerManagementFrameworkBundle\DataTransformer\Mailchimp;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

/**
 * Transforms a Pimcore date to mailchimp date format.
 *
 * Class Date
 *
 * @package CustomerManagementFrameworkBundle\DataTransformer\Mailchimp
 */
class Date implements DataTransformerInterface
{
    /**
     * Examples for formats which exist in Mailchimp:
     * m/d
     * d/m
     * m/d/Y
     * d/m/Y
     *
     * @var string
     */
    private $format;

    public function __construct($format = 'm/d/Y')
    {
        $this->format = $format;
    }

    public function transform($data, $options = [])
    {
        if ($data instanceof \DateTime) {
            return $data->format($this->format);
        }
    }
}
