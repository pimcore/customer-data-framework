# Installation and First Configuration

This section describes the installation of the Customer Management Framework and the first steps of configuration.

## Installation

1) Add dependency for CMF to your composer.json and run composer update. 
```json
    ...
    "require": {
        ...
        "pimcore/customer-management-framework-bundle": "^1",
        ...
   }
   ... 
```

2) Open Pimcore Admin UI, navigate to `Tools` > `Extensions` and activate and install 
`PimcoreCustomerManagementFrameworkBundle` and `ObjectMergerBundle`. 

![Extension Manager](./img/install.jpg)

The installer does following tasks:
* Install several data object classes.
* Create additional tables for activities, deletions, segment building, actions, triggers, rules, duplicates and
  newsletter system export.   
* Add additional permissions.

After successful installation and reload of Pimcore Admin UI an additional customer management menu should be available. 


## Configure Customer Class

The CMF installation does not create a data object class for customers. That is because the framework does not limit you
on specific classes or class structures when it comes to customers. The only requirement is that the customer class 
has to be 'prepared' to be used in CMF context. 

Following options to prepare the customer class are available:
 
* For all basic CMF functionality: The customer class needs to extend the 
  [`CustomerManagementFrameworkBundle\Model\AbstractCustomer`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/AbstractCustomer.php) 
  class. In addition to that, following data attributes need to be available in the customer class:
  * `active`: checkbox
  * `gender`: gender field
  * `firstname`: firstname field
  * `lastname`: lastname field
  * `street`: input field
  * `zip`: input field
  * `city`: input field
  * `countryCode`: country selection
  * `email`: email field
  * `phone`: input field
  * `manualSegments`: objects relation to `CustomerSegments` or objects with metadata to `CustomerSegments` with 
     `created_timestamp` and `application_counter` as numeric meta fields
  * `calculatedSegments`: objects relation to `CustomerSegments` or objects with metadata to `CustomerSegments` with 
     `created_timestamp` and `application_counter` as numeric meta fields
  * `idEncoded`: input field
  
  As starting point this [class definition](..) can be used. 
 
 
* When using customer objects as users for Symfony security: In this case the customer class needs to extend the 
  [`CustomerManagementFrameworkBundle\Model\AbstractCustomer\DefaultAbstractUserawareCustomer`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/AbstractCustomer/DefaultAbstractUserawareCustomer.php) 
  class and also need to have one additional data attribute:
  * `password`: password field
 
* When using the provided [SSO functionality](./Single_Sign_On.md): In this case the customer class additionally needs
  to implement the [`CustomerManagementFrameworkBundle\Model\SsoAwareCustomerInterface`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/SsoAwareCustomerInterface.php)
  interface and also need to have one additional data attribute:   
  * `ssoIdentities`: objects relation to `SsoIdentity`
 
 
* Minimal Requirements (not suggested): If you want to be complete independent, you just need to make sure the Customer 
  Class somehow implements the interface `CustomerManagementFrameworkBundle\Model\CustomerInterface`

 
Of course your customer class can have additional attributes as needed.   
