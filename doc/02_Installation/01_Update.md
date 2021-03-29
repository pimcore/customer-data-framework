# Update Notices

## Update to Version 3
- Removed `SegmentTrackedListener` for tracking tracked segments into Piwik/Matomo 
  (as matomo integration is deprecated in Pimcore 6.9 and removed in Pimcor X)
- Activate `Generate Type Declarations` for all classes generated/used by the Customer Management Framework
  - `Customer`
  - `CustomerSegment`
  - `SsoIdentity`
- `FilterHandler::addFilter` as not operator as parameter anymore (as this was only considered with `SearchQuery` filters). 
  Use new `BoolanCombinator` for combining multiple filters with OR operator instead. 
    

## Update to Pimcore X
- Update to Pimcore 6.9
- Update Customer Management Framework to latest version (compatible to Pimcore 6.9)
- Execute all migrations of Customer Management Framework
- Update to Pimcore X