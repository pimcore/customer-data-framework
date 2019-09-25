# Newsletter Sync

## Provider specific Configs
Have a look at the according doc pages:


[Mailchimp](./Mailchimp/README.md)


[Newsletter2Go](./Newsletter2Go/README.md)


The CMF offers built-in support for synchronizing customer data with MailChimp and Newsletter2Go. It synchronizes configured parts of the 
customer data and optionally also can export mail templates based on Pimcore mail documents. 

> Other newsletter systems could be integrated too by implementing the `NewsletterProviderHandlerInterface`.

## Configuration

The newsletter synchronisation has to be enabled in CMF configuration. Additional configurations are
- `newsletterQueueImmediateAsyncExecutionEnabled`: customer is synchronized with MailChimp on every save. 
- API settings for Mailchimp / Newsletter2Go. 

Further than that, the CMF can handle multiple MailChimp lists. Each list is one separate symfony service tagged with 
`cmf.newsletter_provider_handler`. 

See [Configuration](../03_Configuration.md) for an example configuration of such a service and for a list of general 
newsletter related settings.

## Exporting Customers to Newsletter Providers

### Newsletter Queue

Exporting customer data to Newsletter Providers is handled asynchronously. This means, that each time when a customer get's saved it 
will be added to the newsletter queue (represented by a database table).

A cronjob in the background, which should run every few minutes, then processes the queue items. If they were successfully 
processed, they will be removed from the queue. 

If the sync does not work (e.g. because MailChimp/Newsletter2Go do not respond properly, etc.) they stay in the queue and will be 
executed later on. See also [CronJobs](../04_Cronjobs.md).

##### Immediate execution of export on customer save

Although the export is handled asynchronously it is possible to enable that a queue entry will be processed immediately 
after a customer is saved. This will still run as background task. The queue will be only triggered asynchronously exactly 
for the saved customer. If the export is not successful, the entry will stay in the queue. 

The config option for this behaviour is called `newsletterQueueImmediateAsyncExecutionEnabled` 
(see [Configuration](../03_Configuration.md)).


> **Caution: Pimcore should be the master data base. Therefore no changes of user data (e.g. interest groups) within 
MailChimp should be allowed. The CMF processes updates of simple merge fields but it's much better to disallow such updates. 
Customer segments/interest groups definitely can not be synced back to Pimcore.**


## Exporting newsletter templates

To display the buttons you have to configure the Newsletter provider.
The config option for this is called `enableTemplateExporter` 
(see [Configuration](../03_Configuration.md)).

![template_exporter](../img/mailchimp/mailchimp-export-template.png)

With this it's possible to create the newsletter within Pimcore and then use it for emailing campaigns within the newsletter provider.


## Logging

The CMF logs Customer sync related changes on three different levels:
- Customer activities are tracked on each MailChimp status change
- In the notes and events tab of the customer a list of successful exports will be shown.
- Errors are logged into the application logger.  

# Addressing a customer segment with Pimcore's own newsletter functionality

The CMF comes equipped with its own address source adapter, selectable from the Newsletter Sending Panel in newsletter documents.

![Segment Address Source Adapter](../img/SegmentAddressSource.png)

Here multiple customer segments can be selected to send a newsletter to all customers related to any of them.
For this adapter to become available it must be configured as shown in the example below:

```yml
pimcore:
    newsletter:
        source_adapters:
            SegmentAddressSource: cmf.document.newsletter.factory.segmentAddressSource
```

