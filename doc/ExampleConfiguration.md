# Full featured example configuration

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


    # example newsletter provider handler (mailchimp sync) config
    appbundle.data_transformer.mailchimp.birthdate:
          class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\DataTransformer\Date
          arguments:
            - m/d/Y
            - Y-m-d

    appbundle.mailchimp-handler.list1:
        class: CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp
        autowire: true
        arguments:
            - list1
            - 1ea29442a8
            - manuallySubscribed: subscribed
              singleOptIn: subscribed
              doubleOptIn: subscribed
              unsubscribed: unsubscribed
              pending: pending
            - subscribed: doubleOptIn
              unsubscribed: unsubscribed
              pending: pending

            - firstname: FNAME
              lastname: LNAME
              street: STREET
              birthDate: BIRTHDATE

            - birthDate: '@appbundle.data_transformer.mailchimp.birthdate'

        tags: [cmf.newsletter_provider_handler]

pimcore_customer_management_framework:
    oauth_client:
        enabled:              false

    # Configuration of general settings
    general:
        customerPimcoreClass: Customer
        mailBlackListFile:    /home/customerdataframework/www/var/config/cmf/mail-blacklist.txt

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

    newsletter:
      newsletterSyncEnabled: true

      # if enabled the queue console command for a single item will be executed as background cli command on customer save
      newsletterQueueImmidiateAsyncExecutionEnabled: true

      mailchimp:
          apiKey: d1a46n87d7fsd51f8e98a9decc7b71b9-us15
          cliUpdatesPimcoreUserName: mailchimp-cli

    # Configuration of customer list view
    customer_list:
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
                   
        filter_properties:
            equals:

                id:                  o_id
                active:              active
            search:
                email:
                  - email
                name:
                  - firstname
                  - lastname
                  
                search:
                  - o_id
                  - idEncoded
                  - firstname
                  - lastname
                  - email
                  - zip
                  - city

    # Configuration of customer duplicates services
    customer_duplicates_services:
        duplicateCheckFields:
          - [email]
          - [firstname, lastname]
          
        duplicates_view:
            enabled: true # the feature will be visible in the backend only if it is enabled
            listFields:
              - [id]
              - [email]
              - [firstname, lastname]
              - [street]
              - [zip, city]
              
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
                  similarityTreshold: 90
    
            dataTransformers:
              street: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Street
              firstname: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              city: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              lastname: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Simplify
              birthDate: \CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Date

```