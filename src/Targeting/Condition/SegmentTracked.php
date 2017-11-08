<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Targeting\Condition;

use CustomerManagementFrameworkBundle\Targeting\ActionHandler\TrackSegment;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Targeting\Condition\DataProviderDependentConditionInterface;
use Pimcore\Targeting\DataProvider\Session;
use Pimcore\Targeting\Model\VisitorInfo;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;

class SegmentTracked implements DataProviderDependentConditionInterface
{
    /**
     * @var int|null
     */
    private $segmentId;

    /**
     * @var int
     */
    private $threshold = 1;

    /**
     * @param int|null $segmentId
     * @param int $threshold
     */
    public function __construct(int $segmentId = null, int $threshold = 1)
    {
        if ($threshold < 1) {
            throw new \InvalidArgumentException('Threshold must be at least 1');
        }

        $this->segmentId = $segmentId;
        $this->threshold = $threshold;
    }

    /**
     * @inheritDoc
     */
    public static function fromConfig(array $config)
    {
        return new self(
            $config['segmentId'],
            null !== $config['threshold'] ? (int)$config['threshold'] : 1
        );
    }

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        return [Session::PROVIDER_KEY];
    }

    /**
     * @inheritDoc
     */
    public function canMatch(): bool
    {
        return null !== $this->segmentId;
    }

    /**
     * @inheritDoc
     */
    public function match(VisitorInfo $visitorInfo): bool
    {
        $bag = $visitorInfo->get(Session::PROVIDER_KEY);
        if (!($bag && $bag instanceof NamespacedAttributeBag)) {
            return false;
        }

        $segments = $bag->get(SegmentTracker::KEY_SEGMENTS, []);

        if (isset($segments[$this->segmentId])) {
            return $segments[$this->segmentId] >= $this->threshold;
        }

        return false;
    }
}
