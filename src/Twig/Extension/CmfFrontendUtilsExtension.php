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

namespace CustomerManagementFrameworkBundle\Twig\Extension;

use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use CustomerManagementFrameworkBundle\Helper\JsConfigService;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\User;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CmfFrontendUtilsExtension extends AbstractExtension
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var JsConfigService
     */
    private $jsConfigService;

    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var ExporterManagerInterface
     */
    private $customerExportManager;

    /**
     * CmfFrontendUtilsExtension constructor.
     *
     * @param Packages $packages
     * @param JsConfigService $jsConfigService
     * @param SegmentManagerInterface $segmentManager
     * @param ExporterManagerInterface $customerExportManager
     */
    public function __construct(Packages $packages, JsConfigService $jsConfigService, SegmentManagerInterface $segmentManager, ExporterManagerInterface $customerExportManager)
    {
        $this->packages = $packages;
        $this->jsConfigService = $jsConfigService;
        $this->segmentManager = $segmentManager;
        $this->customerExportManager = $customerExportManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cmf_minifiedAssetUrl', [$this, 'minifiedAssetUrl']),
            new TwigFunction('cmf_jsConfig', [$this, 'getJsConfig']),
            new TwigFunction('cmf_inDebug', [$this, 'inDebug']),
            new TwigFunction('cmf_arrayChunkSize', [$this, 'calculateArrayChunkSize']),
            new TwigFunction('cmf_segmentsForGroup', [$this, 'getSegmentsForGroup']),
            new TwigFunction('cmf_filterSegmentGroups', [$this, 'getFilteredSegmentGroups']),
            new TwigFunction('cmf_userAllowedToUpdate', [$this, 'getUserAllowedToUpdate']),
            new TwigFunction('cmf_userAllowedToShare', [$this, 'getUserAllowedToShare']),
            new TwigFunction('cmf_isCurrentUserFilterSharer', [$this, 'isCurrentUserFilterSharer']),
            new TwigFunction('cmf_loadUsers', [$this, 'getUsers']),
            new TwigFunction('cmf_loadRoles', [$this, 'getRoles']),
            new TwigFunction('cmf_loadExporterConfigs', [$this, 'getExporterConfigs']),
        ];
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters()//: array
    {
        return [
            new TwigFilter('cmf_printFieldCombinations', [$this, 'printFieldCombinations']),
            new TwigFilter('cmf_codeList', [$this, 'buildCodeList'])
        ];
    }

    public function minifiedAssetUrl($url, $packageName = null, $condition = null, $minifiedExtension = 'min'): string
    {
        if (null === $condition) {
            $condition = !\Pimcore::inDebugMode();
        } else {
            if (is_callable($condition)) {
                $condition = call_user_func($condition, $url, $minifiedExtension);
            }
        }

        if (!$condition) {
            return $this->packages->getUrl($url, $packageName);
        }

        $parts = explode('.', $url);

        $extension = array_pop($parts);
        $extension = $minifiedExtension.'.'.$extension;

        $parts[] = $extension;

        return $this->packages->getUrl(implode('.', $parts), $packageName);
    }

    public function getJsConfig(): JsConfigService
    {
        return $this->jsConfigService;
    }

    public function inDebug(): bool
    {
        return \Pimcore::inDebugMode();
    }

    public function calculateArrayChunkSize(array $array)
    {
        $chunkSize = ceil(sizeof($array) / 2);

        return $chunkSize > 0 ? $chunkSize : 1;
    }

    public function getSegmentsForGroup(CustomerSegmentGroup $segmentGroup): array
    {
        return $this->segmentManager->getSegmentsFromSegmentGroup($segmentGroup);
    }

    public function getFilteredSegmentGroups($segmentGroups, $showSegments): array
    {
        $filteredSegmentGroups = [];
        foreach ($segmentGroups as $segmentGroup) {
            if (in_array($segmentGroup->getId(), $showSegments)) {
                $filteredSegmentGroups[] = $segmentGroup;
            }
        }

        return $filteredSegmentGroups;
    }

    public function getUserAllowedToUpdate(FilterDefinition $filterDefinition): bool
    {
        return $filterDefinition->getId() && $filterDefinition->isUserAllowedToUpdate(\Pimcore\Tool\Admin::getCurrentUser());
    }

    public function getUserAllowedToShare(FilterDefinition $filterDefinition): bool
    {
        return $filterDefinition->getId() && $filterDefinition->isUserAllowedToShare(\Pimcore\Tool\Admin::getCurrentUser());
    }

    public function isCurrentUserFilterSharer(): bool
    {
        return FilterDefinition::isFilterSharer(\Pimcore\Tool\Admin::getCurrentUser());
    }

    public function getUsers(): User\Listing
    {
        $listing = new User\Listing();
        $listing->setCondition('`type` = "user"');

        return $listing;
    }

    public function getRoles(): User\Role\Listing
    {
        $listing = new User\Role\Listing();
        $listing->setCondition('`type` = "role"');

        return $listing;
    }

    public function getExporterConfigs(): array
    {
        return $this->customerExportManager->getExporterConfig();
    }

    /** ===================
     *  Filters
     *  ==================== */
    public function printFieldCombinations($fieldCombinations): string
    {
        if ($fieldCombinations) {
            foreach ($fieldCombinations as $key => $combination) {
                $fieldCombinations[$key] = implode(', ', $combination);
            }

            return implode('<br>', $fieldCombinations);
        }

        return '';
    }

    public function buildCodeList(array $values): string
    {
        $codeValues = array_map(function ($value) {
            return '<code>' . $value . '</code>';
        }, $values);

        return implode(' ', $codeValues);
    }
}
