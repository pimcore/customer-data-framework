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

use CustomerManagementFrameworkBundle\Targeting\DataProvider\CustomerSegments;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Targeting\Condition\DataProviderDependentConditionInterface;
use Pimcore\Targeting\Condition\VariableConditionInterface;
use Pimcore\Targeting\DataProvider\TargetingStorage;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\Storage\TargetingStorageInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HasSegment implements DataProviderDependentConditionInterface, VariableConditionInterface
{
    /**
     * @var int|null
     */
    private $segmentId;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param int|null $segmentId
     * @param array $options
     */
    public function __construct(int $segmentId = null, array $options = [])
    {
        $this->segmentId = $segmentId;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'threshold'                => 1,
            'considerCustomerSegments' => true,
            'considerTrackedSegments'  => true
        ]);

        $resolver->setAllowedTypes('threshold', 'int');
        $resolver->setAllowedValues('threshold', function ($value) {
            return $value > 0;
        });

        $resolver->setAllowedTypes('considerCustomerSegments', 'bool');
        $resolver->setAllowedTypes('considerTrackedSegments', 'bool');
    }

    /**
     * @inheritDoc
     */
    public static function fromConfig(array $config)
    {
        return new self(
            $config['segmentId'],
            [
                'threshold'                => $config['threshold'] ?? 1,
                'considerCustomerSegments' => $config['considerCustomerSegments'] ?? true,
                'considerTrackedSegments'  => $config['considerTrackedSegments'] ?? true
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        $providers = [];

        if ($this->options['considerCustomerSegments']) {
            $providers[] = CustomerSegments::PROVIDER_KEY;
        }

        if ($this->options['considerTrackedSegments']) {
            $providers[] = TargetingStorage::PROVIDER_KEY;
        }

        return $providers;
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
        $segments = $this->loadSegments($visitorInfo);

        if (isset($segments[$this->segmentId])) {
            return $segments[$this->segmentId] >= $this->options['threshold'];
        }

        return false;
    }

    private function loadSegments(VisitorInfo $visitorInfo): array
    {
        $segments = [];

        if ($this->options['considerCustomerSegments']) {
            $segments = $this->mergeSegments(
                $segments,
                $visitorInfo->get(CustomerSegments::PROVIDER_KEY)
            );
        }

        if ($this->options['considerTrackedSegments']) {
            $segments = $this->mergeSegments(
                $segments,
                $this->loadTrackedSegments($visitorInfo)
            );
        }

        return $segments;
    }

    private function loadTrackedSegments(VisitorInfo $visitorInfo): array
    {
        /** @var TargetingStorageInterface $storage */
        $storage = $visitorInfo->get(TargetingStorage::PROVIDER_KEY);

        $segments = $storage->get($visitorInfo, SegmentTracker::KEY_SEGMENTS, []);

        return $segments;
    }

    private function mergeSegments(array $segments, array $data): array
    {
        foreach ($data as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
        }

        return $segments;
    }

    /**
     * @inheritDoc
     */
    public function getVariables(VisitorInfo $visitorInfo): array
    {
        return $this->loadSegments($visitorInfo);
    }
}
