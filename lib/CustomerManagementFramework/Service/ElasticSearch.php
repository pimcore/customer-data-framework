<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\Service;

use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;

class ElasticSearch {

    private $elasticSearchClient;
    public function getElasticSearchClient() {

        if(empty($this->elasticSearchClient)) {
            $builder =  \Elasticsearch\ClientBuilder::create();
            if($this->config->enableLogging){
                $logger = \Elasticsearch\ClientBuilder::defaultLogger(PIMCORE_LOG_DIRECTORY . '/es-experience-cloud.log', \Monolog\Logger::DEBUG);
                $builder->setLogger($logger);
            }
            $builder->setHosts(["dev-elasticsearch"]);
            $this->elasticSearchClient = $builder->build();
        }
        return $this->elasticSearchClient;
    }
    
    public function createIndex($indexName) {
        $esClient = $this->getElasticSearchClient();

        $result = $esClient->indices()->exists(['index' => $indexName]);

        if(!$result) {
            $esClient->indices()->create(['index' => $indexName,
                                          'body'  => ['settings' => [
                                              "number_of_shards"   => 5,
                                              "number_of_replicas" => 0

                                          ]]]);
        }
    }

    public function insertActivityIntoExperienceCloud(IActivity $activity) {
        $esClient = $this->getElasticSearchClient();

        $indexName = "rentexperiencecloud2_bookings";

        $this->createIndex($indexName);

        $esClient->index([
            'index' => $indexName,
            'type'  => $activity->cmfGetType(),
            'id'    => $activity->getId(),
            'body'  => $activity->cmfToArray()

        ]);
    }

    public function insertCustomerIntoExperienceCloud(ICustomer $customer) {
        $esClient = $this->getElasticSearchClient();

        $indexName = "rentexperiencecloud2_customers";

        $this->createIndex($indexName);

        $data = $customer->cmfToArray();

        $esClient->index([
            'index' => $indexName,
            'type'  => 'Customer',
            'id'    => $customer->getId(),
            'body'  => $data

        ]);

      /*  $activities = [];
        $bookings = new \Pimcore\Model\Object\Booking\Listing();
        $bookings->setCondition("customer__id = ?", $customer->getId());

        foreach($bookings as $booking) {
            $activities[] = $booking->cmfToArray();
        }*/
       /* $data['activities'] = $activities;


        $esClient->index([
            'index' => $indexName,
            'type'  => 'Customer',
            'id'    => $customer->getId(),
            'body'  => $data

        ]);*/
    }
}