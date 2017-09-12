<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\MailchimpReverse;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

/**
 * Transforms a Pimcore date to mailchimp date format.
 *
 * Class Date
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
        if(!$data) {
            return null;
        }

        return Carbon::createFromFormat($this->format, $data);
    }
}
