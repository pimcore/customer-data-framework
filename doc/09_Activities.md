# Activities

An important part of the customer management framework are customer activities. They can be pretty much every activity a 
customer can perform - and therefore very simple (for example customer logged in) or quite complex (orders, bookings...). 
The CMF can be used to store all customer related activity data and use it for example for segment building or as 
information for the customer service team.


Activities in the CMF are stored all together in a generic json store called ActivityStore - by default a 
MariaDB table (`plugin_cmf_activities`). 


## Using Pimcore Data Objects and other Data Entities as Activities

Additionally to the generic activity store, it's possible to use Pimcore data objects (or other data entities/sources) as 
activities too. They just need to implement the 
[`ActivityInterface`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/ActivityInterface.php) 
(for Pimcore data objects extend the 
[`AbstractObjectActivity`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/AbstractObjectActivity.php) class).

Although these activities have their own data persistence they are additionally stored in the generic ActivityStore. 
The idea is that the ActivityStore saves all activities in a standardized way and represents a history of all customer 
activities. 

So if an activity is for example an order and later on someone cancels this order the order could be a new 
activity within the ActivityStore. So there could be the original order activity and a "cancellation" activity too. 

But for sure it depends on the use case if you need such a detailed history. It's also possible to delete/update the 
original activity in the ActivityStore if necessary.


### Generic activities

As mentioned above, activities can use their own persistence additionally to the ActivityStore but don't have to. 
It's also possible to save activities only in the ActivityStore. The framework includes a generic activity implementation 
which could be used for these cases: 
[`CustomerManagementFrameworkBundle\Model\Activity\GenericActivity`](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/Activity/GenericActivity.php). 

This activity is used as a standard in the [REST API](./Webservice.md) if no `implentationClass` is defined. It can handle 
a nested, associative array of data which is only saved in the ActivityStore. 

But this doesn't mean that only this `GenericActivity` implementation can be used for activities without it's own 
persistence. It definitely makes sense to implement a separate activity class to track logins for example.

How to do so, just have a look at implementations like 
[MailchimpStatusChangeActivity](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/Activity/MailchimpStatusChangeActivity.php)
or [TrackedUrlActivity](https://github.com/pimcore/customer-data-framework/blob/master/src/Model/Activity/TrackedUrlActivity.php). 


## ActivityStore

The ActivityStore is responsible for saving and reading activity data from/into the database. By default a MariaDb 
implementation is used. The attributes of the activities just need to be a valid JSON object. There is no limitation in 
the number of attributes and nesting of attributes. The MariaDB implementation stores the table as dynamic columns. 
This makes it possible to use SQL queries for the attribute fields although they are all together stored in a blob field.
See [docs for dynamic columns](https://mariadb.com/kb/en/mariadb/dynamic-columns/) for details. 

By implementing the [`ActivityStoreInterface`](https://github.com/pimcore/customer-data-framework/blob/master/src/ActivityStore/ActivityStoreInterface.php#L30)
 it is possible to create your own implementation for storing activities (for example in a MongoDB database).


## ActivityManager

On top of the ActivityStore the ActivityManager is responsible for handling activities in the CMF. It's a relatively 
lightweight component which just handles the process of tracking activities. Most of the work is done within the 
ActivityStore - the ActivityManager is just responsible for putting everything together independently of the actual 
implementation of the ActivityStore.

### Tracking an activity
Example:
```php
<?php
// $activity needs to implement ActivityInterface
\Pimcore::getContainer()->get('cmf.activity_manager')->trackActivity($activity);
```


## ActivityView

The CMF plugin contains a list view for displaying activities. This is added as a tab within the customer objects.

![ActivityView](./img/ActivityView.png)


### Handle how activities are displayed in the ActivityView

It's possible to handle how activities are displayed within the ActivityView by implementing the following three methods 
of the `ActivityInterface`:

```php
<?php
/**
 * Returns an associative array with data which should be shown additional to the type and activity date within the 
 * ActivityView overview list.
 * 
 * @param ActivityStoreEntryInterface $entry
 *
 * @return array
 */
public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry);


/**
 * Returns an associative array with data which should be shown ActivityView detail page.
 * 
 * @param ActivityStoreEntryInterface $entry
 *
 * @return array
 */
public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry);


/**
 * Optional: Returns a template file which should be used for the ActivityView detail page. With this it's possible to 
 * implement completely individual detail pages for each activity type.
 * 
 * @param ActivityStoreEntryInterface $entry
 *
 * @return string|bool
 */
public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry);
```
