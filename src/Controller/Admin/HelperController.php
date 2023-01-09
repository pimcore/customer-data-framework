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

use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/helper")
 */
class HelperController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{
    /**
     * get list of customer fields for action trigger rules
     *
     * @param Request $request
     * @Route("/customer-field-list")
     */
    public function customerFieldListAction(Request $request, CustomerProviderInterface $customerProvider)
    {
        $class = ClassDefinition::getById($customerProvider->getCustomerClassId());

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

        return $this->adminJson($result);
    }

    /**
     * get list of available activity types
     *
     * @param Request $request
     * @Route("/activity-types")
     */
    public function activityTypesAction(Request $request, ActivityStoreInterface $activityStore)
    {
        $types = $activityStore->getAvailableActivityTypes();

        $result = [];
        foreach ($types as $type) {
            $result[] = [$type];
        }

        return $this->adminJson($result);
    }

    /**
     * @Route("/grouped-segments")
     *
     * @param SegmentManagerInterface $segmentManager
     *
     * @return JsonResponse
     */
    public function groupedSegmentsAction(SegmentManagerInterface $segmentManager)
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

        return $this->adminJson($segments);
    }

    /**
     * @return Response
     * @Route("/settings-json")
     */
    public function settingJsonAction()
    {
        $settings = [
            'newsletterSyncEnabled' => $this->getParameter('pimcore_customer_management_framework.newsletter.newsletterSyncEnabled'),
            'duplicatesViewEnabled' => $this->getParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_view.enabled'),
            'segmentAssignment' => $this->getParameter('pimcore_customer_management_framework.segment_assignment_classes.types'),
            'customerClassName' => $this->getParameter('pimcore_customer_management_framework.general.customerPimcoreClass'),
            'shortcutFilterDefinitions' => FilterDefinition::prepareDataForMenu(FilterDefinition::getAllShortcutAvailableForUser($this->getAdminUser()))
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
     * @param CustomerProviderInterface $customerProvider
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    public function possibleNewsletterFilterFlagsAction(CustomerProviderInterface $customerProvider)
    {
        $classDefinition = ClassDefinition::getById($customerProvider->getCustomerClassId());

        $possibleFlags = [];
        $fields = $classDefinition->getFieldDefinitions();
        foreach ($fields as $field) {
            if (
                $field instanceof ClassDefinition\Data\Consent ||
                $field instanceof ClassDefinition\Data\NewsletterConfirmed ||
                $field instanceof ClassDefinition\Data\NewsletterActive
            ) {
                $possibleFlags[] = [ 'name' => $field->getName(), 'label' => $field->getTitle() ];
            }
        }

        return $this->json([
            'data' => $possibleFlags
        ]);
    }
}
