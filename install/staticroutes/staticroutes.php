<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 11.01.2017
 * Time: 14:38
 */
return [
    [
        "name" => "cmf-rest-customers",
        "pattern" => "#^/cmf/api/customers/?\$#",
        "reverse" => "/cmf/api/customers",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_customers",
        "action" => "json",
        "variables" => NULL,
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-customers-resource",
        "pattern" => "#^/cmf/api/customers/(\\d+)\$#",
        "reverse" => "/cmf/api/customers/%id",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_customers",
        "action" => "json",
        "variables" => "id",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-segments",
        "pattern" => "#^/cmf/api/segments/?\$#",
        "reverse" => "/cmf/api/segments",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_segments",
        "action" => "json",
        "variables" => NULL,
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-segments-resource",
        "pattern" => "#^/cmf/api/segments/(\\d+)\$#",
        "reverse" => "/cmf/api/segments/%id",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_segments",
        "action" => "json",
        "variables" => "id",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-segment-groups-resource",
        "pattern" => "#^/cmf/api/segment-groups/(\\d+)\$#",
        "reverse" => "/cmf/api/segment-groups/%id",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_segment-groups",
        "action" => "json",
        "variables" => "id",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-segment-groups",
        "pattern" => "#^/cmf/api/segment-groups/?\$#",
        "reverse" => "/cmf/api/segment-groups",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_segment-groups",
        "action" => "json",
        "variables" => NULL,
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-segments-of-customer",
        "pattern" => "#^/cmf/api/segments-of-customer/?\$#",
        "reverse" => "/cmf/api/segments-of-customer",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_segments-of-customers",
        "action" => "json",
        "variables" => NULL,
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-deletions",
        "pattern" => "#^/cmf/api/deletions/?\$#",
        "reverse" => "/cmf/api/deletions",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_deletions",
        "action" => "json",
        "variables" => "",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-activities",
        "pattern" => "#^/cmf/api/activities/?\$#",
        "reverse" => "/cmf/api/activities",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_activities",
        "action" => "json",
        "variables" => NULL,
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ],
    [
        "name" => "cmf-rest-activities-resource",
        "pattern" => "#^/cmf/api/activities/(\\d+)\$#",
        "reverse" => "/cmf/api/activities/%id",
        "module" => "CustomerManagementFramework",
        "controller" => "rest_activities",
        "action" => "json",
        "variables" => "id",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ]
];