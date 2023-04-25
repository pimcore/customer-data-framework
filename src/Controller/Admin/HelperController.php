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

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Bundle\NewsletterBundle\Model\DataObject\ClassDefinition\Data\NewsletterActive;
use Pimcore\Bundle\NewsletterBundle\Model\DataObject\ClassDefinition\Data\NewsletterConfirmed;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/helper")
 */
class HelperController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * get list of customer fields for action trigger rules
     *
     * @Route("/customer-field-list")
     */
    public function customerFieldListAction(Request $request): JsonResponse
    {
        $class = ClassDefinition::getById(\Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassId());

        $result = [];

        foreach ($class->getFieldDefinitions() as $fieldDefinition) {
            $class = get_class($fieldDefinition);

            if (in_array(
                $class,
                [
                    ClassDefinition\Data\Checkbox::class,
                    ClassDefinition\Data\Input::class,
                    ClassDefinition\Data\Select::class,
                    ClassDefinition\Data\Numeric::class,
                    ClassDefinition\Data\Textarea::class,
                    ClassDefinition\Data\Slider::class,
                ]
            )) {
                $result[] = [$fieldDefinition->getName(), $fieldDefinition->getTitle() ?: $fieldDefinition->getName()];
            }
        }

        @usort(
            $result,
            function ($a, $b) {
                return strcmp(strtolower($a[1]), strtolower($b[1]));
            }
        );

        return $this->jsonResponse($result);
    }

    /**
     * get list of available activity types
     *
     * @Route("/activity-types")
     */
    public function activityTypesAction(Request $request): JsonResponse
    {
        $types = \Pimcore::getContainer()->get('cmf.activity_store')->getAvailableActivityTypes();

        $result = [];
        foreach ($types as $type) {
            $result[] = [$type];
        }

        return $this->jsonResponse($result);
    }

    /**
     * @Route("/grouped-segments")
     */
    public function groupedSegmentsAction(SegmentManagerInterface $segmentManager): JsonResponse
    {
        $segments = [];

        foreach ($segmentManager->getSegmentGroups() as $group) {
            $groupSegments = $segmentManager->getSegmentsFromSegmentGroup($group);

            foreach ($groupSegments as $groupSegment) {
                $segments[] = [
                    'id' => $groupSegment->getId(),
                    'name' => $groupSegment->getName(),
                    'groupId' => $group->getId(),
                    'groupName' => $group->getName()
                ];
            }
        }

        return $this->jsonResponse($segments);
    }

    /**
     * @Route("/settings-json")
     */
    public function settingJsonAction(): Response
    {
        $settings = [
            'newsletterSyncEnabled' => $this->getParameter('pimcore_customer_management_framework.newsletter.newsletterSyncEnabled'),
            'duplicatesViewEnabled' => $this->getParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_view.enabled'),
            'segmentAssignment' => $this->getParameter('pimcore_customer_management_framework.segment_assignment_classes.types'),
            'customerClassName' => $this->getParameter('pimcore_customer_management_framework.general.customerPimcoreClass'),
            'shortcutFilterDefinitions' => FilterDefinition::prepareDataForMenu(FilterDefinition::getAllShortcutAvailableForUser($this->getPimcoreUser()))
        ];

        $content = '
            pimcore = pimcore || {};
            pimcore.settings = pimcore.settings || {};
            pimcore.settings.cmf = ' . json_encode($settings) . ';
        ';

        return new Response($content, 200, ['content-type' => 'application/javascript']);
    }

    /**
     * @Route("/newsletter/possible-filter-flags")
     *
     * @throws \Exception
     */
    public function possibleNewsletterFilterFlagsAction(CustomerProviderInterface $customerProvider): JsonResponse
    {
        $classDefinition = ClassDefinition::getById($customerProvider->getCustomerClassId());

        $possibleFlags = [];
        $fields = $classDefinition->getFieldDefinitions();
        foreach ($fields as $field) {
            if (
                $field instanceof ClassDefinition\Data\Consent ||
                $field instanceof NewsletterConfirmed ||
                $field instanceof NewsletterActive
            ) {
                $possibleFlags[] = [ 'name' => $field->getName(), 'label' => $field->getTitle() ];
            }
        }

        return $this->json([
            'data' => $possibleFlags
        ]);
    }
}
