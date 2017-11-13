# Customer Management Framework Bundle for Pimcore

Pimcore allows to manage any kind of data - unstructured, digital assets and structured content. The most obvious 
structured content is product data and all data that is related to products like categories, technologies, brands, etc. 
The built-in E-Commerce-Framework supports with building e-commerce applications and managing transactional data like 
orders.

The third big group of structured data besides products and transaction is customer data. 
The Customer Management Framework (CMF) for Pimcore adds additional functionality  for customer data management, 
segmentation, personalization and marketing automation. So it allows to aggregate customer data and user profiles, 
enrich data, link social profiles, build audience segments, trigger events, personalize experience, execute marketing 
automation and much more.

Like Pimcore itself and the E-Commerce-Framework, the CMF is also a Framework for developers to develop customer management
solutions and is tightly integrated into Pimcore core functionality. 

## Provided Functionality in a Nutshell
- Customer Data Management based on Pimcore data objects 
- Storage of Customer Activities from different data feeds
- Customer Segmentation based on data, activities and other criteria
- Duplicates detection and merging
- SSO and connection to social profiles like Google, Facebook, Twitter, etc. 
- Enhancement of Pimcore's on-site personalization
- Marketing Automation with built-in rule engine and triggers
- Default integration to external newsletter systems like MailChimp
- Rest API for integration of external systems for data import or data export

For a first impression have a look at our [Advanced Demo](https://demo-advanced.pimcore.org/). For more complex solutions
have a look at our [case studies](https://pimcore.com/en/customers).


## Working with Customer Management Framework 

Following aspects are short cuts into the documentation for start working with the Customer Management Framework (CMF): 

* [Architecture Overview](./doc/01_Architecture-Overview.md)
* [Installation](./doc/02_Installation.md) and [Configuration](./doc/03_Configuration.md)
* [Working with Customers](./doc/05_Working-with-Customers.md)
* [Working with Activities (ActivityManager, ActivityStore, ActivityView)](./doc/09_Activities.md)
* [Working with Customer Segments](./doc/11_CustomerSegments.md)
* [Customer Duplicates Service](./doc/15_CustomerDuplicatesService.md)
* [Working with integrated Single Sign On](./doc/18_Single_Sign_On.md)
* [Built-In Marketing Automation Engine](./doc/22_ActionTrigger.md)
* [Newsletter System Integration](./doc/24_NewsletterSync.md)
* [Rest API Webservice](./doc/26_Webservice.md)


## Contributing and Development

For details see our [Contributing guide](CONTRIBUTING.md).
