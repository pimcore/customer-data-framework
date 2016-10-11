<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\IActivity;
use Pimcore\Db;
use Sabre\DAVACL\IACL;

class DefaultActivityManager implements IActivityManager
{

    /**
     * @param IActivity $activity
     *
     * @return void
     */
    
    public function trackActivity(IActivity $activity) {
        //$this->instertIntoLocalDb();
        $this->insertIntoExperienceCloud($activity);
    }

    protected function insertIntoExperienceCloud(IActivity $activity) {
        $esService = Factory::getInstance()->getElasticSearchService();
        $esClient = $esService->getElasticSearchClient();

       // $esService->createIndex();
        //$esService->insertActivityIntoExperienceCloud($activity);

        $mariadbService = Factory::getInstance()->getMariaDbService();
        $mariadbService->insertActivityIntoDb($activity);

    }


}