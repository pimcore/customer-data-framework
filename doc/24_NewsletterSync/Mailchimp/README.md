## Prepare Data Objects for MailChimp Sync

### Customer

It's needed to add a `newsletterStatus` and `mailchimpStatus` field into the customer class for each MailChimp list:  

![Customer newsletter status](../../img/mailchimp/customer-newsletter-status.png)

The fields need to be named with the shortcut of the associated provider handler as a suffix (e.g. `newsletterStatusList1` 
and `mailchimpStatusList1`). 

The newsletter status is a project-specific setting. A mapping between the status options and the MailChimp status options 
need to be done in the service definition of the provider handler.

The MailChimp status needs to be a read only field with the following options:
- `subscribed`
- `unsubscribed`
- `pending`
- `cleaned`


If the MailChimp sync feature is enabled, the Customer data object class needs to implement the `MailchimpAwareCustomerInterface`. 

This interface offers one method: 
`public function needsExportByNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);`


This method could be used to decide, if a customer needs to be exported to a given MailChimp list (most of the time you will do 
this by the configured shortcut of the MailChimp provider handler). With this mechanism it's possible to create different 
lists with different customers based on some kind of rules. 


### CustomerSegmentGroup
Besides customers, also customer segments can be exported as interest groups to MailChimp. To configure that, add a checkbox 
called `exportNewsletterProvider{PROVIDER_HANDLER_SHORTCUT}` for each list to the `CustomerSegmentGroup` class. 
See below for more details.

### Webhook 

The CMF offers a MailChimp webhook endpoint to receive updates from MailChimp. The webhook is implemented as webservice 
and is handled the same way as the REST webservice in the Pimcore core.

So the steps to enable the webhook are as following:
- Enable the Pimcore core webservice feature.
- Create a Pimcore user for handling the webhook (e.g. "mailchimp-webhook"). 
- Generate an api key for the Pimcore user.
- Add a webhook in the MailChimp web interface with the following URL: 
  `https://mydomain.com/webservice/cmf/mailchimp/webhook?apikey=53c5f6f3427545e712fe59ce043489f86ee0eb4b64a7c098d89d4288167eec1c`
  The webhook needs to be configured like this:
  ![Webhook options](../../img/mailchimp/mailchimp-webhook-options.png)
  
### Cronjob for syncing data from MailChimp to Pimcore

The webhook makes it possible that updates in MailChimp are synced in more or less real time to Pimcore. But sometimes 
this might not work (for example when the server is down). Therefore the CMF offers an additional cronjob, which could 
run once a day and synchronize needed updates to Pimcore. See also [CronJobs - Mailchimp status sync](../../04_Cronjobs.md).


## Exporting CustomerSegments to MailChimp

Customer segment exports are handled by the newsletter queue too (see above). MailChimp has a limit of 60 interest groups 
(the equivalent for CustomerSegments in MailChimp) per list. Therefore the CMF offers a configuration option for what 
CustomerSegmentGroups should be exported (all segments within this group will be exported).

![Webhook options](../../img/mailchimp/mailchimp-export-segment-group.png)

It's necessary to add a checkbox `exportNewsletter{PROVIDER_HANDLER_SHORTCUT}` for each provider handler (mailchimp list) 
into the CustomersSegmentGroup data objects.