<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Listing\Filter;

class FloatBetween extends AbstractFieldBetween
{
    /**
     * @var float
     */
    protected $from;

    /**
     * @var float
     */
    protected $to;

    /**
     * @param string $field
     * @param float|null $from
     * @param float|null $to
     */
    public function __construct($field, $from = null, $to = null)
    {
        parent::__construct($field);

        if (null !== $from) {
            $this->from = (float)$from;
        }

        if (null !== $to) {
            $this->to = (float)$to;
        }
    }

    /**
     * @return float|null
     */
    protected function getFromValue()
    {
        return $this->from;
    }

    /**
     * @return float|null
     */
    protected function getToValue()
    {
        return $this->to;
    }
}
