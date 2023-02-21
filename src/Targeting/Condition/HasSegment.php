<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Targeting\Condition;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\CustomerSegments;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Condition\AbstractVariableCondition;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProvider\TargetingStorage;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Storage\TargetingStorageInterface;
use Pimcore\Model\DataObject\CustomerSegment;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HasSegment extends AbstractVariableCondition implements DataProviderDependentInterface
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
            'operator' => '>=',
            'value' => 1,
            'considerCustomerSegments' => true,
            'considerTrackedSegments' => true
        ]);

        $resolver->setAllowedTypes('operator', 'string');
        $resolver->setAllowedValues('operator', ['%', '=', '<', '<=', '>', '>=']);

        $resolver->setAllowedTypes('value', 'int');
        $resolver->setAllowedValues('value', function ($value) {
            return $value > 0;
        });

        $resolver->setAllowedTypes('considerCustomerSegments', 'bool');
        $resolver->setAllowedTypes('considerTrackedSegments', 'bool');
    }

    /**
     * @inheritDoc
     */
    public static function fromConfig(array $config): self
    {
        $segmentId = null;
        if (is_numeric($config['segment'])) {
            $segmentId = (int)$config['segment'];
        } else {
            // TODO load from segment manager?
            $segment = CustomerSegment::getByPath($config['segment']);
            if ($segment instanceof CustomerSegmentInterface) {
                $segmentId = $segment->getId();
            }
        }

        return new self(
            $segmentId,
            [
                'operator' => $config['condition_operator'] ?? '>=',
                'value' => $config['value'] ?? 1,
                'considerCustomerSegments' => $config['considerCustomerSegments'] ?? true,
                'considerTrackedSegments' => $config['considerTrackedSegments'] ?? true
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
            $result = $this->matchCondition(
                $segments[$this->segmentId],
                $this->options['operator'],
                $this->options['value']
            );

            if ($result) {
                $this->setMatchedVariables([$this->segmentId => $segments[$this->segmentId]]);
            }

            return $result;
        }

        return false;
    }

    private function matchCondition(int $segmentCount, string $operator, int $value): bool
    {
        switch ($operator) {
            case '%':
                return $segmentCount % $value === 0;

            case '=':
                return $segmentCount === $value;

            case '>':
                return $segmentCount > $value;

            case '>=':
                return $segmentCount >= $value;

            case '<':
                return $segmentCount < $value;

            case '<=':
                return $segmentCount <= $value;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported operator "%s"', $operator));
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

        $segments = $storage->get(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_VISITOR,
            SegmentTracker::KEY_SEGMENTS,
            []
        );

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
}
