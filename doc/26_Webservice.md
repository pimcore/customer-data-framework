# Webservice

The CMF plugin has a built in REST webservice. Access is handled via API-Keys and works the same way as in the Pimcore core:
[Pimcore core REST Webservice API](https://www.pimcore.org/docs/latest/Web_Services/index.html)

## API Reference

### Customers API

The customers API implements standard REST calls for customer CRUD actions:

| Path                    | Method | Description                 |
|-------------------------|--------|-----------------------------|
| /webservice/cmf/customers      | GET    | Fetch all customers         |
| /webservice/cmf/customers/{id} | GET    | Fetch a single customer     |
| /webservice/cmf/customers      | POST   | Create a new customer       |
| /webservice/cmf/customers/{id} | PATCH  | Partially update a customer |
| /webservice/cmf/customers/{id} | DELETE | Delete a customer           |

The `GET` requests can be filtered by passing the following params as query params:

| Parameter             | Possible Values               | Description                                                 |
| --------------------- |-----------------------------  |-----------------------------------------------------------  |
| includeActivities     | true/false                    | include activities of customer into result set              |
| segments              | comma-separated list of IDs   | filter by segments                                          |
| page                  | int                           | page number for paging                                      |
| pageSize              | int                           | page size   for paging                                      |


### Activities API

The activities API implements standard REST calls for activity CRUD actions:

| Path                    | Method | Description                   |
|-------------------------|--------|-------------------------------|
| /webservice/cmf/activities      | GET    | Fetch all activities         |
| /webservice/cmf/activities/{id} | GET    | Fetch a single activity      |
| /webservice/cmf/activities      | POST   | Create a new activity        |
| /webservice/cmf/activities/{id} | PATCH  | Partially update a activity  |
| /webservice/cmf/activities/{id} | DELETE | Delete a activity            |

The `GET` requests can be filtered by passing the following params as query params:

| Parameter             | Possible Values               | Description                                                    |
| --------------------- |-----------------------------  |--------------------------------------------------------------- |
| type                  | string                        | filter by activity type                                        |
| modifiedSinceTimestamp| timestamp/int                 | get activities which where modified since given timestamp      |
| page                  | int                           | page number for paging                                         |
| pageSize              | int                           | page size   for paging                                         |

### Deletions API

The deletions API delivers information about deletions of customers and activities:

| Path                     | Method | Description                   |
|--------------------------|--------|-------------------------------|
| /webservice/cmf/deletions       | GET    | Fetch all segments            |

The request can be filtered by passing the following params as query params:

| Parameter               | Possible Values               | Description                                                    |
| ----------------------- |-----------------------------  |--------------------------------------------------------------- |
| entityType              | customers/activities          | get deletions of customers or activities                       |
| deletionsSinceTimestamp | timestamp/int                 | get deletions since given timestamp only                       |


### Segments API

The segments API implements standard REST calls for customer segment CRUD actions:

| Path                     | Method | Description                   |
|--------------------------|--------|-------------------------------|
| /webservice/cmf/segments        | GET    | Fetch all segments            |
| /webservice/cmf/segments/{id}   | GET    | Fetch a single segment        |
| /webservice/cmf/segments        | POST   | Create a new segment          |
| /webservice/cmf/segments/{id}   | PATCH  | Partially update a segment    |
| /webservice/cmf/segments/{id}   | DELETE | Delete a segment              |

The `GET` requests can be filtered by passing the follwing params as query params:

| Parameter             | Possible Values               | Description                                                    |
| --------------------- |-----------------------------  |--------------------------------------------------------------- |
| page                  | int                           | page number for paging                                         |
| pageSize              | int                           | page size   for paging                                         |

### Segment groups API

The segment groups API implements standard REST calls for customer segment group CRUD actions:

| Path                           | Method | Description                           |
|--------------------------------|--------|---------------------------------------|
| /webservice/cmf/segment-groups        | GET    | Fetch all segment groups              |
| /webservice/cmf/segment-groups/{id}   | GET    | Fetch a single segment group          |
| /webservice/cmf/segment-groups        | POST   | Create a new segment group            |
| /webservice/cmf/segment-groups/{id}   | PATCH  | Partially update a segment group      |
| /webservice/cmf/segment-groups/{id}   | DELETE | Delete a segment group                |

The `GET` requests can be filtered by passing the following params as query params:

| Parameter             | Possible Values               | Description                                                    |
| --------------------- |-----------------------------  |--------------------------------------------------------------- |
| page                  | int                           | page number for paging                                         |
| pageSize              | int                           | page size   for paging                                         |

### Segments of customers API

The segments of customers API allows to add segments to customers and remove segments form customers:

| Path                           | Method | Description                           |
|--------------------------------|--------|---------------------------------------|
| /webservice/cmf/segments-of-customers | POST    | Add/remove segments                   |

``` 
Example POST body JSON:
{
    "customerId": 12345,
    "addSegments": [123,456],
    "removeSegments": [567,789]
}
```