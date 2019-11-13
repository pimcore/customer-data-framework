<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Import\CustomerImportService;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\ImportConfig;
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
    public function customerFieldListAction(Request $request)
    {
        $class = \Pimcore\Model\DataObject\ClassDefinition::getById(\Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassId());

        $result = [];

        foreach ($class->getFieldDefinitions() as $fieldDefinition) {
            $class = get_class($fieldDefinition);

            if (in_array(
                $class,
                [
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::class,
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Input::class,
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Select::class,
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::class,
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Textarea::class,
                    \Pimcore\Model\DataObject\ClassDefinition\Data\Slider::class,
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
    public function activityTypesAction(Request $request)
    {
        $types = \Pimcore::getContainer()->get('cmf.activity_store')->getAvailableActivityTypes();

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
                    'id'        => $groupSegment->getId(),
                    'name'      => $groupSegment->getName(),
                    'groupId'   => $group->getId(),
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
    public function settingJsonAction(CustomerImportService $importService)
    {


        $customerClassId = null;
        if($class = ClassDefinition::getByName($this->getParameter('pimcore_customer_management_framework.general.customerPimcoreClass'))) {
            $customerClassId = $class->getId();
        }

        $customerImporterId = $this->getParameter('pimcore_customer_management_framework.import.customerImporterId');

        if(!$importService->isImporterIdAllowed($customerImporterId, $customerClassId)) {
            $customerImporterId = 0;
        }


        $templateExporters = [];
        if($this->container->hasParameter('pimcore_customer_management_framework.newsletter.mailchimp.enableTemplateExporter')) {
            if($this->container->getParameter('pimcore_customer_management_framework.newsletter.mailchimp.enableTemplateExporter')) {
                $templateExporters['mailchimp'] = \CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\TemplateExporter::class;
            }
        }

        if($this->container->hasParameter('pimcore_customer_management_framework.newsletter.newsletter2Go.enableTemplateExporter')) {
            if($this->container->getParameter('pimcore_customer_management_framework.newsletter.newsletter2Go.enableTemplateExporter')) {
                $templateExporters['newsletter2Go'] = \CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go\TemplateExporter::class;
            }
        }


        $settings = [
            'newsletterSyncEnabled' => $this->container->getParameter('pimcore_customer_management_framework.newsletter.newsletterSyncEnabled'),
            'templateExporters' => $templateExporters,
            'duplicatesViewEnabled' => $this->container->getParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_view.enabled'),
            'segmentAssignment' => $this->getParameter('pimcore_customer_management_framework.segment_assignment_classes.types'),
            'customerClassName' => $this->getParameter('pimcore_customer_management_framework.general.customerPimcoreClass'),
            'customerClassId' => $customerClassId,
            'customerImporterId' => $customerImporterId,
            'customerImportParentId' => $this->getParameter('pimcore_customer_management_framework.import.customerImportParentId'),
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function possibleNewsletterFilterFlagsAction(CustomerProviderInterface $customerProvider) {

        $classDefinition = ClassDefinition::getById($customerProvider->getCustomerClassId());

        $possibleFlags = [];
        $fields = $classDefinition->getFieldDefinitions();
        foreach ($fields as $field) {

            if(
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
