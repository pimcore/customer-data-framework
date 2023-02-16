# CronJobs

Following is a list of all cronjobs needed by the CMF bundle. Depending on the project requirements, the execution interval 
might be different and some of the cronjobs might not be needed at all.  


### Segment Building Queue
Handles the calculation of asynchronous segments by processing the [segment building queue](./11_CustomerSegments.md). 
This is needed for segments which could not be calculated directly for performance reasons.

```
* * * * * php /home/project/www/bin/console.php cmf:build-segments -v > /home/project/www/log/cmf-build-segments-queue-lastrun.log 
``` 

### Action trigger queue
Handles the execution of delayed actions in [ActionTrigger rules](./22_ActionTrigger.md).

```
* * * * * php /home/project/www/bin/console.php cmf:process-actiontrigger-queue -v > /home/customerdataframework/www/website/var/log/cmf-process-actiontrigger-queue-lastrun.log 
```

### Cron Trigger
This cronjob is needed if cron triggers are used in [ActionTrigger rules](./22_ActionTrigger.md). Important: this needs to run once 
per minute!

```
* * * * * php /home/project/www/bin/console.php cmf:handle-cron-triggers -v > /home/project/www/log/cmf-cron-trigger-lastrun.log 
```

### Calculate potential duplicates
Analyzes the [duplicates index](./15_CustomerDuplicatesService.md) and calculates potential duplicates which will be 
shown in the potential duplicates list view. 

```
* * * * * php /home/project/www/bin/console.php cmf:duplicates-index -c -v > /home/project/www/log/cmf-potential-duplicates-lastrun.log 
```

### CMF Maintenance
This cronjob should be configured to be executed on a regular basis. It performs various tasks configured in `services.yml` 
 and tagged with `cmf.maintenance.serviceCalls`.  

```
* * * * * php /home/project/www/bin/console.php cmf:maintenance -v > /home/project/www/log/cmf-cron-maintenance-lastrun.log 
```

### Newsletter Queue
Processes the newsletter queue. This job should run once every x minutes (e.g. every 5 minutes) when the newsletter/mailchimp sync feature is needed.

```
* * * * * php /home/project/www/bin/console.php cmf:newsletter-sync -c > /home/project/www/log/cmf-newsletter-sync-lastrun.log 
```


### Mailchimp status sync
Should run as a night job. Synchronizes status updates from Mailchimp to Pimcore if webhook calls failed. This is important to ensure data integrity also when the system is down for several hours.
Setup a Pimcore user name (e.g. mailchimp-cli) in the CMF config - this user will be visible in the versions history (see [Configuration](./03_Configuration.md)). The CLI user needs no special rights - it's just needed to identify the updates in the versions history.
```
* * * * * php /home/project/www/bin/console.php cmf:newsletter-sync -m > /home/project/www/log/cmf-mailchimp-status-sync-lastrun.log 
```
