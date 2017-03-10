# Customer Duplicates Service

## Samples:

### create a new customer instance
```php
$customer = new Customer();
$customer->setBirthDate(new Date('1982-12-07'));
$customer->setFirstname("Markus");
$customer->setLastname("Moser");
$customer->setZip("5020");
$customer->setPublished(true);
$customer->setActive(true);
```

### get an object list with duplicates for the new customer instance (set limit to 1)
```php
$duplicates = Factory::getInstance()->getCustomerDuplicatesService()->getDuplicatesOfCustomer($customer, 1);
```

### if duplicates exist and "checkForDuplicates" is activated in the CMF config file, an exception will be thrown when trying to save the new customer and a duplicate exists.
```php
try {
    $customer->save();
} catch(DuplicateCustomerException $e) {
    print "save failed - duplicate found: " . $e->getDuplicateCustomer() . PHP_EOL;
}
```

### get duplicates of an existing customer
```php
$existingCustomer = Customer::getById(12345);
$duplicates = Factory::getInstance()->getCustomerDuplicatesService()->getDuplicatesOfCustomer($existingCustomer, 1);
```
