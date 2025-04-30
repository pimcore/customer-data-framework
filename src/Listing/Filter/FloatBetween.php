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
