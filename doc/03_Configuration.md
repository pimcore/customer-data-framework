# Configuration of CMF

Being a framework, there are a lot of settings in the CMF. These settings can be configured in a combination of 
container service definitions for certain tags and a symfony config tree. 

Following you see a full featured example of the configuration. For details of a functionality see corresponding sections
in this documentation.  

## Services

e.g. in `services.yml`: 

```yaml


services:
    # example customer save handler config
    appbundle.cmf.customer_save_handler.normalize_zip:
       class: CustomerManagementFrameworkBundle\CustomerSaveHandler\NormalizeZip
       tags: [cmf.customer_save_handler]

    # example segment builder config
    appbundle.cmf.segment_builder.state:
         class: CustomerManagementFrameworkBundle\SegmentBuilder\StateSegmentBuilder
         tags: [cmf.segment_builder]


    # example newsletter provider handler (mailchimp sync) config for one Mailchimp receiver list
    appbundle.cmf.mailchimp.birthdate-transformer:
          class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Date
          arguments:
            - m/d/Y
            - Y-m-d

    # Special data transformer to handle multi-value merge field.
    appbundle.cmf.mailchimp.address-addr1-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - addr1
    appbundle.cmf.mailchimp.address-addr2-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - addr2
    appbundle.cmf.mailchimp.address-zip-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - zip
    appbundle.cmf.mailchimp.address-city-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - city
        - true
    appbundle.cmf.mailchimp.address-state-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - state
        - true
    appbundle.cmf.mailchimp.address-country-transformer:
      class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Address
      arguments:
        - country
        - true

    appbundle.cmf.mailchimp.handler.list1:
        class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp
        autowire: true
        arguments:
            # Shortcut of the handler/list for internal use
            - list1
            
            # List ID within Mailchimp
            - ls938393f
            
            # Mapping of Pimcore status field => Mailchimp status
            - manuallySubscribed: subscribed
              singleOptIn: subscribed
              doubleOptIn: subscribed
              unsubscribed: unsubscribed
              pending: pending
              
            # Reverse mapping of Mailchimp status => Pimcore status field
            - subscribed: doubleOptIn
              unsubscribed: unsubscribed
              pending: pending

            # Mapping of Pimcore data object attributes => Mailchimp merge fields
            - firstname: FNAME
              lastname: LNAME
              # See the data transformer below why we can map muliple fields to 
              # the same merge field.
              street: ADDRESS
              zip: ADDRESS
              city: ADDRESS
              countryCode: ADDRESS
              birthDate: BIRTHDATE

            # Special data transformer for the birthDate field. 
            # This ensures that the correct data format will be used.
            - birthDate: '@appbundle.cmf.mailchimp.birthdate-transformer'
              # Special data transformer for the multi-value field ADDRESS. 
              street: '@appbundle.cmf.mailchimp.address-addr1-transformer'
              zip: '@appbundle.cmf.mailchimp.address-zip-transformer'
              city: '@appbundle.cmf.mailchimp.address-city-transformer'
              countryCode: '@appbundle.cmf.mailchimp.address-country-transformer'

        tags: [cmf.newsletter_provider_handler]
        
    
```        


## Configuration Tree   

e.g. in `config.yml`: 

```yaml
pimcore_customer_management_framework:
   
    # Configuration of general settings
    general:
        customerPimcoreClass: Customer
        mailBlackListFile:    /home/customerdataframework/www/var/config/cmf/mail-blacklist.txt

    
    # Newsletter/MailChimp sync related settings
    newsletter:
        newsletterSyncEnabled: true
        
        # Immediate execution of customer data export on customer save. 
        newsletterQueueImmediateAsyncExecutionEnabled: true

        mailchimp:
          apiKey: d1a40ajzf41d5154455a9455cc7b71b9-us14
          cliUpdatesPimcoreUserName: mailchimp-cli

    # Configuration of EncryptionService
    encryption:

        # echo \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
        # keep it secret
        secret:               'def00000a2fe8752646f7d244c950f0399180a7ab1fb38e43edaf05e0ff40cfa2bbedebf726268d0fc73d5f74d6992a886f83eb294535eb0683bb15db9c4929bbd138aee'

    # Configuration of customer save manager
    customer_save_manager:

        # If enabled the automatic object naming scheme will be applied on each customer save. See: customer_provider -> namingScheme option
        enableAutomaticObjectNamingScheme: false

    # Configuration of customer provider
    customer_provider:

        # parent folder for active customers
        parentPath:           /customers

        # parent folder for customers which are unpublished and inactive
        archiveDir:           /customers/_archive

        # If a naming scheme is configured customer objects will be automatically renamend and moved to the configured folder structure as soon as the naming scheme gets applied.
        namingScheme:         '{countryCode}/{zip}/{firstname}-{lastname}' 
        
        # Parent folder for customers which are created via the "new customer" button in the customer list view
        newCustomersTempDir:         /customers/_temp

    # Configuration of customer save manager
    customer_save_validator:

        # If enabled an exception will be thrown when saving a customer object if duplicate customers exist.
        checkForDuplicates:   false
        requiredFields:

            # Provide valid field combinations. The customer object then is valid as soon as at least one of these field combinations is filled up.
            - [email]
            - [firstname, lastname]

    # Configuration of segment manager
    segment_manager:
        segmentFolder:

            # parent folder of manual segments + segment groups
            manual:               /segments/manual

            # parent folder of calculated segments + segment groups
            calculated:           /segments/calculated

    activity_url_tracker:
          enabled: true
          # used for automatic link generation of LinkActivityDefinition data objects
          linkCmfcPlaceholder: '*|ID_ENCODED|*'
     
    # Configuration for segment assignment
    segment_assignment_classes:
          types:
              document:
                  page: true
                  email: true
              asset:
                  image: true
              object:
                  object:
                    Product: true
                    ShopCategory: true
                  folder: true

    # Configuration of customer list view
    customer_list:
    
        # configure exporters available in customer list
        exporters:
            csv:
                name:                 CSV # Required
                icon:                 'fa fa-file-text-o' # Required
                exporter:             '\CustomerManagementFrameworkBundle\CustomerList\Exporter\Csv' # Required
                exportSegmentsAsColumns: true
                properties:           
                   - id
                   - active
                   - gender
                   - email
                   - phone
                   - firstname
                   - lastname
                   - street
                   - zip
                   - city
                   - countryCode
                   - idEncoded
            
            xlsx:
                name:                 XLSX # Required
                icon:                 'fa fa-file-excel-o' # Required
                exporter:             '\CustomerManagementFrameworkBundle\CustomerList\Exporter\Xlsx' # Required
                exportSegmentsAsColumns: true
                properties:           
                   - id
                   - active
                   - gender
                   - email
                   - phone
                   - firstname
                   - lastname
                   - street
                   - zip
                   - city
                   - countryCode
                   - idEncoded
              
        # Configuration of filters in the customer list view. The properties configured here will 
        # be handled if passed as ?filter[] query parameter.
        filter_properties:
            # Filter fields which must match exactly.
            equals:
                # ?filter[id]=8 will result in a SQL condition of "WHERE id=8"
                id:                  id
                active:              active
                
            # Searched fields in customer view search filters
            # (enhanced search syntax (AND/OR/!/*...) could be used in these fields).
            # Search will be applied to all fields in the list, e.g. 
            # ?filter[name]=val will result in a SQL condition of "WHERE (firstname LIKE "%val%" OR lastname LIKE "%val")
            # See https://github.com/pimcore/search-query-parser for detailed search syntax. 
            search:
                # email search filter
                email:
                  - email
                  
                # name search filter
                firstname:
                  - firstname
                      
                lastname: 
                  - lastname
                  
                # main search filter
                search:
                  - id
                  - idEncoded
                  - firstname
                  - lastname
                  - email
                  - zip
                  - city

    # Configuration of customer duplicates services
    customer_duplicates_services:
    
        # Field or field combinations for hard duplicate check
        duplicateCheckFields:
            - [email]
            - [firstname, lastname]
        
        # Performance improvement: add duplicate check fields which are trimmed (trim() called on the field value) by a 
        # customer save handler. No trim operation will be needed in the resulting query.
        duplicateCheckTrimmedFields:
            - email
            - firstname
            - lastname
        
        duplicates_view:
            enabled: true # the feature will be visible in the backend only if it is enabled
            # Visible fields in the customer duplicates view. 
            # Each single group/array is one separate column in the view table.
            listFields:
              - [id]
              - [email]
              - [firstname, lastname]
              - [street]
              - [zip, city]
              
        # Index used for a global search of customer duplicates. 
        # Matching field combinations can be configured here.
        # See "Customer Duplicates Service" docs chapter for more details.
        duplicates_index:
            enableDuplicatesIndex: false
            duplicateCheckFields:
                - firstname:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText

                  zip:
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\Zip

                  street:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText

                  birthDate:
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\BirthDate::class
                
                - lastname:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText

                  firstname:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText

                  zip:
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\Zip

                  city:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText

                  street:
                      soundex: true
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText
                
                
                - email:
                      metaphone: true
                      similarity: \CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText
                      similarityThreshold: 90
    
            dataTransformers:
              street: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Street
              firstname: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              city: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              lastname: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              birthDate: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Date
```
