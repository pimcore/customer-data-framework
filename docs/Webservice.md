# Webservice

The CMF plugin has a built in REST webservice. Access is handled via API-Keys and works the same way as in the Pimcore core:
[Pimcore core REST Webservice API](https://www.pimcore.org/docs/latest/Web_Services/index.html)

## Export Api

#### customers
___
/cmf/api/export/customers

###### available URL parameter options
| Parameter             |Possible Values                | Description                                                 |
| --------------------- |-----------------------------  |-----------------------------------------------------------  |
| includeActivities     |true/false                     | include activities of customer into result set              |
| segments              |comma-separated list of IDs    | filter by segments                                          |
___

#### activites

/cmf/api/export/activities
___
#### delitions (of customers and activities)

/cmf/api/export/deletions
___
#### segments

/cmf/api/export/segments
___
#### segment groups

/cmf/api/export/segment-groups
___
## Import Api

#### segments

/cmf/api/import/segment
___
#### segment groups

/cmf/api/import/segment-group