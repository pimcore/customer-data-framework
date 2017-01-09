# ActivityManager + ActivityStore

Activities in the customer data framework are stored all together in a json store - by default in a MariaDB table (plugin_cmf_activities). 

## Using Pimcore objects and other data entities as activities
It's possible to use Pimcore objects (or other data entities/sources) as activities too. They just need to implement the ActivityInterface (for Pimcore objects extend the AbstractActivity class).

But allthough these activities have it's own data persistance they are additionally stored in the generic ActivityStore. The idea is that the ActivityStore saves all activities in a standardized form and represents a history of all customer activities. So if an activity is for example an order and later on someone cancels this order the order could be an own activity within the ActivityStore. So there could be the original order activity and an "cancellation" activity too. But for sure it depends on the use case if you need such a detailed history. It's possible to delete/update the original activity too in the ActivityStore.
