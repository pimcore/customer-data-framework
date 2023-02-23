# Update Notices

## Update to Version 4
- Execute SQL script `src/Resources/sql/segmentAssignment/storedFunctionObject.sql`, only for Pimcore 11.
- Remove Single Sign On custom implementations & classes e.g. `SSOIdentity`, `OAuth1Token`, `OAuth2Token` and `ssoIdentities` field
  in Customer class.

## Update to Version 3
- Activate `Generate Type Declarations` for all classes generated/used by the Customer Management Framework:
  - `Customer`
  - `CustomerSegment`
  - `CustomerSegmentGroup`
  - `SsoIdentity`
- Migrate all templates to twig.
- Add following line to your firewalls configuration in the `security.yml` of your app after the `pimcore_admin` firewall.
```yml 
security:
    firewalls:
        pimcore_admin: 
            # ...
        cmf_webservice: '%customer_management_framework.firewall_settings%'
``` 
- Webservices URLs changed to ` /__customermanagementframework/webservice/*`
- Execute all migrations of Customer Management Framework.

### Additional code changes (that might affect your application)
- Migrated `SearchQueryParser\QueryBuilder\ZendCompatibility` to `Doctrine\DBAL\Query\QueryBuilder`.
- Migrated `Zend\Paginator` to `Knp\Component\Pager`.  
- `FilterHandler::addFilter` has no operator as parameter anymore (as this was only considered with `SearchQuery` filters). 
  Use new `BoolanCombinator` for combining multiple filters with OR operator instead. 
- `AbstractObjectActivity::toArray` and GDPR exporter results might be different, as it utilizes new `normalize` methods 
  instead of deprecated `getDataForWebservice` methods.

### Removed features in Version 3   
- Removed `SegmentTrackedListener` for tracking tracked segments into Piwik/Matomo
  (as matomo integration is deprecated in Pimcore 6.9 and removed in Pimcore X).
- Migrated all templates to php templates and removed templating helpers.
- CSV Importer integration as it is also removed from Pimcore X. Use 
  [Pimcore Data Importer](https://github.com/pimcore/data-importer) instead.


## Update to Pimcore X
- Update to Pimcore 6.9.
- Update Customer Management Framework to latest version (compatible to Pimcore 6.9).
- Execute all migrations of Customer Management Framework.
- Update to Pimcore X.
