# Customer Save Manager
 
The customer save manager is responsible for all actions/hooks which are excuted when a customer object gets saved. It consists of several parts:
 
## Customer save handlers

Customer save handlers are special PHP classes which are executed when a customer gets saved. It's possible to add multiple customer save handlers to one project. Take a look at the CustomerSaveHandlerInterface to find out how they could be implemented. The interface constists of methods for each pimcore object event (preAdd, postAdd, preUpdate etc.). Additionally it's possible to get the original customer object from the database. This could be usefull if you would like to compare if some fields got changed.

Customer save handlers need to be registered as symfony services with the tag "cmf.customer_save_handler".

###### Example service definitions
```yaml
services:
   

   appbundle.cmf.customer_save_handler.normalize_zip:
       class: CustomerManagementFrameworkBundle\CustomerSaveHandler\NormalizeZip
       tags: [cmf.customer_save_handler]
```

### Built in customer save handlers

The CMF offers the following customer save handlers out of the box (but they need to be enabled as services):

#### Cleanup\Email
Removes invalid characters from an email field.

#### NormalizeZip
Tries to normalize zip numbers. For example A-1010 would become 1010. It offers zip correction regexes for several counties. It would be possible to extend the logic for other countries.

#### RemoveBlacklistedEmails
Sets the email field to a empty value if the given email adress is in a defined blacklist. 

#### MarkEmailAddressAsValid
Marks an email address as valid if it has a valid format. Marking as valid means that a special checkbox get checked. 

## Automatic object naming scheme
The CMF automatically applies a naming scheme for customer objects depending on a configured logic. This automatic naming scheme could be disabled if not needed.
 
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

If the CMF is configured like this example all customer objects would be automatically saved within the folder /customers as sub folders starting with the countryCode of the customer object then the zip code as second level and the customer object itself would get a object key with "{firstname}-{lastname}". The CMF automatically would add some postfixes if a customer object with the same key exist in the folder hierarchy.
 
There are two customer folders which could be configured. "parentPath" is the regular customer folder and "archiveDir" will be applied for customers which are unpublished and inactive.

## Customer save validator
If enabled the customer save validator will throw exceptions if the customer is invalid according to it's implementation. These exception could be used in the could within try/catch blocks in order to check if the customer is valid. In the pimcore backend an error message will alert if somebody tries to save an invalid customer.

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

In this example a customer will be validated on save by 2 different ways. 
First it would be checked if either the email adress or the field combination "firstname+name+zip" is filled up. It's possible to define 1 to x field combinations here.
Second the CMF also searches for duplicate customers and declines saving the customer if duplicates exist. Here again the applied field combinations could be configured.
 
## Save customer with disabled hooks

In most cases it's sufficent to just call `$customer->save()` to save a customer object.
Sometimes it's needed to save a customer without validaton or without appling for example customer save handlers or segment builders.

The customer mananagement framework offers a special SaveOptions class to handle the enabled state of all hooks when a customer gets saved.

**Caution: only disable parts of the save options if you are sure that it's needed!**

###### Examples
```php
    $customer = Customer::getById(1234);

    // Disable all hooks and also Pimcore versioning.
    $customer->saveDirty();

    // Disable all hooks but enable Pimcore versioning.
    $customer->saveDirty(true);

    // globally disable on save segment building and also the segment builder queue
    $customer->getSaveManager()->getSaveOptions()
        ->disableOnSaveSegmentBuilders()
        ->disableSegmentBuilderQueue();

    // save customer with disabled object naming scheme but let the global state untouched
    // (getSaveOptions(true) will deliver a cloned instance of the save options)
    $saveOptions = $customer->getSaveManager()->getSaveOptions(true)
                        ->disableObjectNamingScheme();
    $customer->saveWithOptions($saveOptions);

    // save customer with enabled object naming scheme even if it is disabled by default in the config
    $saveOptions = $customer->getSaveManager()->getSaveOptions(true)
                        ->enableObjectNamingScheme();
    $customer->saveWithOptions($saveOptions);

```


