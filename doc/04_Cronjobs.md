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
Handles the execution of delayed actions in [ActionTrigger rules](ActionTrigger.md).

```
* * * * * php /home/project/www/bin/console.php cmf:process-actiontrigger-queue -v > /home/customerdataframework/www/website/var/log/cmf-process-actiontrigger-queue-lastrun.log 
```

### Cron Trigger
This cronjob is needed if cron triggers are used in [ActionTrigger rules](ActionTrigger.md). Important: this needs to run once 
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
This crontob should be configured to be executed on a regular basis. It performs various tasks configured in `services.yml` 
 and tagged with `cmf.maintenance.serviceCalls`.  

```
* * * * * php /home/project/www/bin/console.php cmf:maintenance -v > /home/project/www/log/cmf-cron-maintenance-lastrun.log 
```

