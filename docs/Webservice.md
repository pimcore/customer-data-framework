# Webservice

The CMF plugin has a built in REST webservice. Access is handled via API-Keys and works the same way as in the Pimcore core:
[Pimcore core REST Webservice API](https://www.pimcore.org/docs/latest/Web_Services/index.html)

## Implementation

### API Handlers

CMF API handling is delegated from a controller to an API handler implementing the `HandlerInterface`:

```php
<?php

namespace CustomerManagementFramework\RESTApi;

interface HandlerInterface
{
   /**
    * @param \Zend_Controller_Request_Http $request
    * @return Response
    *
    * @throws Exception\ExceptionInterface
    * @throws \RuntimeException
    */
   public function handle(\Zend_Controller_Request_Http $request);
}
```

A handler is expected to read a request and return a `CustomerManagementFramework\RESTApi\Response` object. How this is
done and which action and methods are supported is defined by the handler itself. 

The framework provides a specialized handler (`AbstractRoutingHandler`) which is able to dispatch the request to multiple
actions which are matched based on a routing definition. Route definition and handling is done through the
[Symfony Routing Component](http://symfony.com/doc/current/components/routing.html). See the `CustomersHandler` and the
`AbstractCrudRoutingHandler` as examples on how to define and handle routes.

### Extending existing API handlers with custom actions

Use the DI configuration to inject a custom handler instead of the framework one. In case of a routing handler, override
the `getRoutes` method and add your custom routes or modify the existing ones.


## API Reference

### Customers API

The customers API implements standard REST calls for customer CRUD actions:

| Path                    | Method | Description                 |
|-------------------------|--------|-----------------------------|
| /cmf/api/customers      | GET    | Fetch all customers         |
| /cmf/api/customers/{id} | GET    | Fetch a single customer     |
| /cmf/api/customers      | POST   | Create a new customer       |
| /cmf/api/customers/{id} | PATCH  | Partially update a customer |
| /cmf/api/customers/{id} | DELETE | Delete a customer           |

The `GET` requests can be filtered by passing the follwing params as query params:

| Parameter             | Possible Values               | Description                                                 |
| --------------------- |-----------------------------  |-----------------------------------------------------------  |
| includeActivities     | true/false                    | include activities of customer into result set              |
| segments              | comma-separated list of IDs   | filter by segments                                          |


### Activities API

The activities API implements standard REST calls for activity CRUD actions:

| Path                    | Method | Description                   |
|-------------------------|--------|-------------------------------|
| /cmf/api/activities      | GET    | Fetch all activities         |
| /cmf/api/activities/{id} | GET    | Fetch a single activity      |
| /cmf/api/activities      | POST   | Create a new activity        |
| /cmf/api/activities/{id} | PATCH  | Partially update a activity  |
| /cmf/api/activities/{id} | DELETE | Delete a activity            |

The `GET` requests can be filtered by passing the follwing params as query params:

| Parameter             | Possible Values               | Description                                                    |
| --------------------- |-----------------------------  |--------------------------------------------------------------- |
| type                  | string                        | filter by activity type                                        |
| modifiedSinceTimestamp| timestamp/int                 | get activities which where modified since given timestamp      |

### Deletions API

The deletions API delivers information about deletions of customers and activities:

| Path                     | Method | Description                   |
|--------------------------|--------|-------------------------------|
| /cmf/api/deletions       | GET    | Fetch all segments            |

The request can be filtered by passing the follwing params as query params:

| Parameter               | Possible Values               | Description                                                    |
| ----------------------- |-----------------------------  |--------------------------------------------------------------- |
| entityType              | customers/activities          | get deletions of customers or activities                       |
| deletionsSinceTimestamp | timestamp/int                 | get deletions since given timestamp only                       |


### Segments API

The segments API implements standard REST calls for customer segment CRUD actions:

| Path                     | Method | Description                   |
|--------------------------|--------|-------------------------------|
| /cmf/api/segments        | GET    | Fetch all segments            |
| /cmf/api/segments/{id}   | GET    | Fetch a single segment        |
| /cmf/api/segments        | POST   | Create a new segment          |
| /cmf/api/segments/{id}   | PATCH  | Partially update a segment    |
| /cmf/api/segments/{id}   | DELETE | Delete a segment              |

### Segment groups API

The segment groups API implements standard REST calls for customer segment group CRUD actions:

| Path                           | Method | Description                           |
|--------------------------------|--------|---------------------------------------|
| /cmf/api/segment-groups        | GET    | Fetch all segment groups              |
| /cmf/api/segment-groups/{id}   | GET    | Fetch a single segment group          |
| /cmf/api/segment-groups        | POST   | Create a new segment group            |
| /cmf/api/segment-groups/{id}   | PATCH  | Partially update a segment group      |
| /cmf/api/segment-groups/{id}   | DELETE | Delete a segment group                |

### Segments of customers API

The segments of customers API allows to add segments to customers and remove segments form customers:

| Path                           | Method | Description                           |
|--------------------------------|--------|---------------------------------------|
| /cmf/api/segments-of-customers | POST    | Add/remove segments                   |

``` 
Example POST body JSON:
{
    "customerId": 12345,
    "addSegments": [123,456],
    "removeSegments": [567,789]
}
```