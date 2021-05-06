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

namespace CustomerManagementFrameworkBundle\DataTransformer\MailchimpReverse;

use Carbon\Carbon;
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

    public function __construct($format = 'Y-m-d')
    {
        $this->format = $format;
    }

    public function transform($data, $options = [])
    {
        if (!$data) {
            return null;
        }

        return Carbon::createFromFormat($this->format, $data);
    }
}
