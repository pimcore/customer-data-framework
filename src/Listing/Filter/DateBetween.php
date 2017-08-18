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

class DateBetween extends AbstractFieldBetween
{
    /**
     * @var \DateTime
     */
    protected $from;

    /**
     * @var \DateTime
     */
    protected $to;

    /**
     * @param string $field
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     */
    public function __construct($field, \DateTime $from = null, \DateTime $to = null)
    {
        parent::__construct($field);

        $this->setFrom($from);
        $this->setTo($to);
    }

    /**
     * @param \DateTime|null $from
     *
     * @return $this
     */
    public function setFrom(\DateTime $from = null)
    {
        if (null !== $from) {
            $from = clone $from;
            $from->setTime(0, 0, 0);
        }

        $this->from = $from;

        return $this;
    }

    /**
     * @param \DateTime|null $to
     *
     * @return $this
     */
    public function setTo(\DateTime $to = null)
    {
        if (null !== $to) {
            $to = clone $to;
            $to->setTime(23, 59, 59);
        }

        $this->to = $to;

        return $this;
    }

    /**
     * @return int|null
     */
    protected function getFromValue()
    {
        return (null !== $this->from) ? $this->from->getTimestamp() : null;
    }

    /**
     * @return int|null
     */
    protected function getToValue()
    {
        return (null !== $this->to) ? $this->to->getTimestamp() : null;
    }
}
