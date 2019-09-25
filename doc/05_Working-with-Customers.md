# Working with Customers

As already described in the installation chapter, customers are simple data objects that need to implement certain
interfaces or extend certain abstract classes. 

As a result, working with customers is pretty much like working with Pimcore data objects. But there are three aspects to consider: 
- It is recommended to use `Customer` as class name. If another name should be used, this has to be configured. 
  For details see [Configuration chapter](03_Configuration.md).
- `CustomerProviderInterface`: An implementation of the 
  [`CustomerProviderInterface`](https://github.com/pimcore/customer-data-framework/blob/master/src/CustomerProvider/CustomerProviderInterface.php#L20) 
  is registered as service `CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface` and provides 
  an abstraction for CRUD operations on customer data objects. This is especially handy since it considers the configured
  class name for the customer class.      
- `CustomerSaveManager` is responsible for all actions/hooks which are executed when a customer object is saved and can 
  be configured in configuration. Its main components are
    - Customer Save Handlers
    - Automatic Object Naming Scheme
    - Customer Save Validator 
    - Save Customer with Disabled Hooks
  
  For Details see [Customer Save Manager Chapter](./06_CustomerSaveManager.md) of the docs. 

Other than that - just use the customer object like every other data object in Pimcore.

In addition to customers, there are two additional data entities provided by the CMF
- [Customer Activities](./09_Activities/README.md): For storing all kind of activities of customers like registering for newsletters, 
  placing orders, etc. 
- [Customer Segments](./11_CustomerSegments.md): For segmenting customers and grouping them together by interests or behavior.  
 