<?php

namespace CustomerManagementFramework\RESTApi;

use Symfony\Component\Routing\RouteCollection;

abstract class AbstractCrudRoutingHandler extends AbstractRoutingHandler implements CrudHandlerInterface
{
    /**
     * @inheritDoc
     */
    protected function getRoutes()
    {
        $routes = new RouteCollection();

        $routes->add(
            'list',
            $this->createRoute('GET', '/', 'listRecords')
        );

        $routes->add(
            'read',
            $this
                ->createRoute('GET', '/{id}', 'readRecord')
                ->setRequirement('id', '\d+')
        );

        $routes->add(
            'create',
            $this->createRoute('POST', '/', 'createRecord')
        );

        $routes->add(
            'update',
            $this
                ->createRoute('PUT', '/{id}', 'updateRecord')
                ->setRequirement('id', '\d+')
        );

        $routes->add(
            'delete',
            $this
                ->createRoute('DELETE', '/{id}', 'deleteRecord')
                ->setRequirement('id', '\d+')
        );

        return $routes;
    }
}
