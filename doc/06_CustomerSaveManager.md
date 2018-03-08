# Customer Save Manager
 
The customer save manager is responsible for all actions/hooks which are executed when a customer object is saved. 
It consists of several parts:
 
## Customer Save Handlers

Customer save handlers special PHP classes that are executed when a customer gets saved and can be used to normalize, 
validate, optimize or modify customers on save.

Customer save handlers need to be registered as symfony services with the tag `"cmf.customer_save_handler"`. 
So it is possible to add multiple customer save handlers to one project. Take a look at the 
[`CustomerSaveHandlerInterface`](https://github.com/pimcore/customer-data-framework/blob/master/src/CustomerSaveHandler/CustomerSaveHandlerInterface.php) 
for how to implement them. The interface consists of methods for each Pimcore object event (`preAdd`, 
`postAdd`, `preUpdate` etc.). 

Additionally it is possible to get the original customer object from the database. This is handy to compare if some fields 
got changed.


###### Example service definition
```yaml
services:
  
   appbundle.cmf.customer_save_handler.normalize_zip:
       class: CustomerManagementFrameworkBundle\CustomerSaveHandler\NormalizeZip
       tags: [cmf.customer_save_handler]
```

### Built in customer save handlers

The CMF offers the following customer save handlers out of the box. They are all located in the namespace 
`CustomerManagementFrameworkBundle\CustomerSaveHandler` and need to be enabled as services by adding 
a corresponding service with the tag `cmf.customer_save_handler` to the container. Also have a look at their constructors
to see possible configuration options. 

#### Cleanup\Email
Removes invalid characters from an email field.

#### NormalizeZip
Tries to normalize zip numbers. For example A-1010 would become 1010. It offers zip correction regexes for several countries. 
It would be possible to extend the logic for other countries.

#### SalutationToGender
Maps a salutation field to a gender field. This can automatically adjust the gender based on the salutation.

#### RemoveBlacklistedEmails
Sets the email field to an empty value if the given email address is in a defined blacklist. 

#### MarkEmailAddressAsValid
Marks an email address as valid if it has a valid format. Marking as valid means that a special checkbox get checked. 

#### AttributeLogic
Allows to setup a logic for overwriting field values based on other field values.

##### Example:
```yaml
appbundle.cmf.customer_save_handler.attribute_logic:
   class: CustomerManagementFrameworkBundle\CustomerSaveHandler\AttributeLogic
   arguments:
      - from: profileStreet
        to: street
        overwriteIfNotEmpty: true
      - from: profileZip
        to: zip
        overwriteIfNotEmpty: true
```

In this example "street" will be overwritten if "profileStreet" changes (the same for zip and profileZip).
If `overwriteIfNotEmpty` is set to false the to field will be overwritten only when it's empty.

**Important:** The field value of the to-field will be overwritten only if the from field changed during the current save process and the to-field value did not change.

## Automatic Object Naming Scheme
The CMF automatically applies a naming scheme for customer objects depending on a configured logic. This automatic naming 
scheme can be disabled if not needed.
 
###### Example configuration
```yaml
pimcore_customer_management_framework:
    customer_save_manager:
        enableAutomaticObjectNamingScheme: true

    customer_provider:
       parentPath: /customers
       archiveDir: /customers/_archive
       namingScheme: '{countryCode}/{zip}/{firstname}-{lastname}'
```

If the CMF is configured like this example, all customer objects would be automatically saved within the folder `/customers` 
as sub folders starting with the `countryCode` of the customer object then the `zip` code as second level and the customer 
object itself would get a object key with `{firstname}-{lastname}`. The CMF automatically will add some postfixes if a 
customer object with the same key exists in the folder hierarchy.
 
There are two customer folders which can be configured. `parentPath` is the regular customer folder and `archiveDir` 
will be applied for customers which are unpublished and inactive.


## Customer Save Validator
If enabled the customer save validator will throw exceptions when the customer is invalid according to it's implementation. 
These exceptions can be used in try/catch blocks in order to check if the customer is valid. In the Pimcore backend an 
error message will alert if somebody tries to save an invalid customer.

###### Example configuration
```yaml
pimcore_customer_management_framework:
    customer_save_validator:
          checkForDuplicates: true
          requiredFields: 
            -  [email],
            -  [firstname, name, zip]

            
    customer_duplicates_services:
          duplicateCheckFields:
            - [email]
            - [firstname, lastname, zip, street]
```

In this example a customer will be validated during save by 2 different ways. 
1) First it would be checked if either the `email address` or the field combination `firstname+name+zip` is filled up. 
  It's possible to define 1 to x field combinations here.
2) Second the CMF also searches for duplicate customers and declines saving the customer if duplicates exist. Here again 
  the applied field combinations can be configured.
 
 
## Save Customer with Disabled Hooks

In most cases it's sufficient to just call `$customer->save()` to save a customer object.
Sometimes it's needed to save a customer without validation or without applying for example customer save handlers or 
segment builders.

The CMF offers a special `SaveOptions` class to handle the enabled state of all hooks when a customer gets saved.

> **Caution: only disable parts of the save options if you are sure that it is needed!**

###### Examples
```php
<?php
    $customer = Customer::getById(1234);

    // Disable all hooks and also Pimcore versioning.
    $customer->saveDirty();

    // Disable all hooks but enable Pimcore versioning.
    $customer->saveDirty(false);

    // Globally disable on save segment building and also the segment builder queue
    $customer->getSaveManager()->getSaveOptions()
        ->disableOnSaveSegmentBuilders()
        ->disableSegmentBuilderQueue();

    // Save customer with disabled object naming scheme but let the global state untouched
    // (`getSaveOptions(true)` will deliver a cloned instance of the save options)
    $saveOptions = $customer->getSaveManager()->getSaveOptions(true)
                        ->disableObjectNamingScheme();
    $customer->saveWithOptions($saveOptions);

    // Save customer with enabled object naming scheme even if it is disabled by default in the config
    $saveOptions = $customer->getSaveManager()->getSaveOptions(true)
                        ->enableObjectNamingScheme();
    $customer->saveWithOptions($saveOptions);

```


