# Customer Segments

*Customer segmentation is the practice of dividing a customer base into groups of individuals that are similar in specific ways relevant to marketing, such as age, gender, interests and spending habits.*

The customer management framework includes tools for creating and managing customer segments and segment groups. Customer segments (and groups) are regular pimcore objects.

## Manual vs. calculated segments

The customer object contains two separate fields for manual and calculated segments. Manuals segments are added within the Pimcore backend by drag and drop. Calculated segments could be added by the CMF within the segment building process.

![manual vs calculated segments](./img/Segments.png)

## SegmentManager

The SegmentManager is responsible for managing, creating and reading CustomerSegments and CustomerSegmentGroups within the CMF. Take a look at the SegmentManagerInterface there you will find inline PHP docs for each contained method.

### Segment builders

Segment builders are PHP classes which need to implement the SegmentBuilderInterface. They could be used to create automaticially calculated segments based on the customer data. For example it would be possible to create a SegmentBuilder "Age" which devides customers into segments of age groups depending on a birth day field.

#### Execute on customer save vs. async

SegmentBuilders could be implemented to be either executed directly on customer save but it's also possible to calculate them asynchronously via a cron job. Take a look at the executeOnCustomerSave() method of the SegmentBuilderInterface. If this returns true it's executed directly on customer save otherwise each customer change is added to a queue and later on the segmentation will be done by the cron job.
 
In the SegmentManagerInterface there is a method addCustomerToChangesQueue() which could be used to trigger customer changes. This needs to be done everytime a customer/SegmentBuilder related data record get's changed. By default this is done on customer object save and also when a new customer activity got tracked.

#### Create your own SegmentBuilder

As mentioned above they need to implement the SegmentBuilderInterface. Most of the work is done in the `prepare(SegmentManagerInterface $segmentManager)` and `calculateSegments(CustomerInterface $customer, SegmentManagerInterface $segmentManager)` methods.

`prepare()` is called when a SegmentBuilder gets prepared. Later on it will be executed with `calculateSegments`. So add in `prepare()` code which needs to be done once for all customers (for example some initializations etc.).
`calculateSegments` then does the actual work: it calculates the segment for the given customer and adds/removes the calculated segments according to the implemented logic.

Both of the methods could interact with the SegmentManager for creating segments and adding/removing them to the customer.

SegmentManager sample calls:
```php
// create a segment called "male" within the segment group "gender". The segment group "gender" will be created too if it doesn't exist.
$segment = $segmentManager->createCalculatedSegment("male", "gender");

// get all other segments of the segment group "gender" (but exclude "male")
$deleteSegments = $segmentManager->getSegmentsFromSegmentGroup($segment->getGroup(),[$segment]);

// add the segment to the customer and remove all other segments from the segment group "gender). The param "GenderSegmentBuilder" is an optional comment which will be added to the notes/events tab of the customer.
$addSegments = [$segment];
$segmentManager->mergeSegments($customer, $addSegments, $deleteSegments, "GenderSegmentBuilder");
```

#### Built in segment builders

Segment builders need to be added to the CMF plugin config file. Only segment builders which are configured there will be executed. The CMF framework includes some SegmentBulders which could be used out of the box:
 
##### AgeSegmentBuilder

Calculates age range segments based on a birthday field. 

| configuration option  | description                                                                                                                             |
| ----------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| segmentGroup          | name of the segment group                                                                                                               |
| birthDayField         | name of the birthday field in the customer object                                                                                       |
| ageGroups             | array of arrays to define the used age groups. Example: [[0,50],[51,100]] would result in an age group 0-50 and another one with 51-100 |


##### GenderSegmentBuilder

Calculates segments based on the gender field. 

| configuration option  | description                                                                                                                             |
| ----------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| segmentGroup          | name of the segment group                                                                                                               |
| maleSegmentName       | name of the male segment                                                                                                                |
| femaleSegmentName     | name of the female segment                                                                                                              |
| notsetSegmentName     | name of the segment if the gender of the customer is not male or female                                                                 |


##### StateSegmentBuilder

Calculates state segments based on the zip field and zip ranges for each state. It currently works for AT, DE and CH 

| configuration option  | description                                                                                                                                                         |
| ----------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| segmentGroup          | name of the segment group                                                                                                                                           |
| countryTransformers   | define the data transformers which should be used for each country - the data transformer is responsible to convert the zip to a state based on it's implementation |

## Console commands

build segments for customers in the changes queue: 
```
php pimcore/cli/console.php cmf:build-segments
```
 
build segments for all customers: 
```
php pimcore/cli/console.php cmf:build-segments -f
```

build segment x for all customers: 
```
php pimcore/cli/console.php cmf:build-segments --segmentBuilder='\Website\CustomerManagementFramework\SegmentBuilder\Brand'
```