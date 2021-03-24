# Multiple Mailchimp Accounts

Optionally it's possible to configure the service container to be able to handle multiple mailchimp accounts. Principally it's handled the same way like newsletter lists via mailchimp provider handler services (see [Configuration](../03_Configuration.md) for an example definition)*[]: 

But additionally to the standard constructor arguments of the mailchimp provider handler class it's needed to setup a specialized export service which holds the Mailchimp client with the alternative API key.

An example config might look like this:

```yaml
  appbundle.cmf.mailchimp.client:
    class: DrewM\MailChimp
    arguments:
      - 'my-example-api-key'
  
  appbundle.cmf.mailchimp.export-service:
    class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\MailChimpExportService
    arguments:
      - '@appbundle.cmf.mailchimp.client'

  appbundle.cmf.mailchimp.handler.list1:
    class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp
    autowire: true
    arguments:
        # Shortcut of the handler/list for internal use
        $shortcut: list1
        
        # List ID within Mailchimp
        $listId: ls938393f
        
        # Mapping of Pimcore status field => Mailchimp status
        $statusMapping:
          manuallySubscribed: subscribed
          singleOptIn: subscribed
          doubleOptIn: subscribed
          unsubscribed: unsubscribed
          pending: pending
          
        # Reverse mapping of Mailchimp status => Pimcore status field
        $reverseStatusMapping:
          subscribed: doubleOptIn
          unsubscribed: unsubscribed
          pending: pending

        # Mapping of Pimcore data object attributes => Mailchimp merge fields
        $mergeFieldMapping:
          firstname: FNAME
          lastname: LNAME
          street: STREET
          birthDate: BIRTHDATE

        # Special data transfromer for the birthDate field. 
        # This ensures that the correct data format will be used.
        $fieldTransformers:
          birthDate: '@appbundle.cmf.mailchimp.birthdate-transformer'
          
        $exportService: '@appbundle.cmf.mailchimp.export-service'

    tags: [cmf.newsletter_provider_handler]
        
```