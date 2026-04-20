---
title: Customer Management Framework
---

> [!IMPORTANT]
> ## This Repository Has Been Archived
>
> This bundle has moved to the Pimcore Enterprise Edition. The GPL version is **EOL** and will no longer receive updates.
>
> - **Enterprise repository:** The updated and supported version is available at [ee-customer-data-framework](https://github.com/pimcore/ee-customer-data-framework) (access is granted by your Pimcore contact person when you have a valid enterprise subscription).
> - **LTS support:** For long-term support, please use our support portal at [get.support.pimcore.com](https://get.support.pimcore.com/) (access is granted by your Pimcore contact person when you have a valid enterprise subscription).
> - **Community support:** For community help and general questions, head over to [Pimcore Discussions](https://github.com/orgs/pimcore/discussions).



# Pimcore Customer Management Framework

Pimcore allows to manage any kind of data - unstructured, digital assets and structured content. The most obvious structured content is product data and all data related to products like categories, technologies, brands, etc. 
The built-in E-Commerce Framework supports building e-commerce applications and managing transactional data like orders. The third big group of structured data besides products and transactions is customer data. 

The **Customer Management Framework (CMF)** for Pimcore adds new functionality for customer data management, segmentation, personalization and marketing automation. It allows aggregating customer data and user profiles, enriching data, linking social profiles, building audience segments, triggering events, personalizing experience, executing marketing automation, and much more.

Like Pimcore itself and the E-Commerce-Framework, the CMF is also a Framework for developers to develop customer management solutions and is tightly integrated into the Pimcore Core functionality. 

## Provided Functionality in a Nutshell
- Customer Data Management based on Pimcore Data Objects.
- Storage of Customer Activities from different data feeds.
- Customer Segmentation based on data, activities and other criteria.
- Duplicate detection and merging.
- Enhancement of Pimcore's on-site personalization.
- Marketing Automation with built-in rule engine and triggers.
- Default integration to external newsletter systems like MailChimp.
- Rest API for integration of external systems for data import or export.

For a first impression have a look at our [Demo](https://demo.pimcore.fun/). For more complex solutions have a look at our [case studies](https://pimcore.com/en/customers).


## Documentation Overview

The following sections provide shortcuts into the documentation to start working with the Customer Management Framework (CMF) for Pimcore: 
- See the [Getting Started](#getting-started) section for an overview of the CMF Framework architecture or information about the installation process.
- See the [Customer-Related Data](#customer-related-data) section for details about the management of customer-related data with the CMF Framework.
- See the [Provided Services](#provided-services) section for documentation about services provided by the CMF Framework.

### Getting Started
* [Architecture Overview](./doc/01_Architecture-Overview.md)
* [Installation](./doc/02_Installation/README.md) and [Configuration](./doc/03_Configuration.md)

### Customer-Related Data
* [Working with Customers](./doc/05_Working-with-Customers.md)
* [Working with Activities (ActivityManager, ActivityStore, ActivityView)](./doc/09_Activities/README.md)
* [Working with Customer Segments](./doc/11_CustomerSegments.md)

### Provided services
* [Customer Duplicates Service](./doc/15_CustomerDuplicatesService.md)
* [Built-In Marketing Automation Engine](./doc/22_ActionTrigger.md)
* [Newsletter System Integration](./doc/24_NewsletterSync/README.md)
* [Rest API Webservice](./doc/26_Webservice.md)
* [Integration with Pimcore Targeting Engine](./doc/30_Personalization/README.md)


## Contributing and Development

For details see our [Contributing guide](https://github.com/pimcore/customer-data-framework/blob/master/CONTRIBUTING.md).
