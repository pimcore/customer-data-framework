<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-02
 * Time: 18:21
 */

namespace CustomerManagementFramework\DuplicatesIndex;

use CustomerManagementFramework\DataSimilarityMatcher\BirthDate;
use CustomerManagementFramework\DataSimilarityMatcher\DataSimilarityMatcherInterface;
use CustomerManagementFramework\DataTransformer\DataTransformerInterface;
use CustomerManagementFramework\DataTransformer\DuplicateIndex\Standard;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Db;
use Pimcore\Logger;
use Psr\Log\LoggerInterface;

class DefaultMariaDbDuplicatesIndex implements DuplicatesIndexInterface {

    const DUPLICATES_TABLE = 'plugin_cmf_duplicatesindex';
    const DUPLICATES_CUSTOMERS_TABLE = 'plugin_cmf_duplicatesindex_customers';
    const POTENTIAL_DUPLICATES_TABLE = 'plugin_cmf_potential_duplicates';
    const FALSE_POSITIVES_TABLE = 'plugin_cmf_duplicates_false_positives';

    protected $config;
    protected $duplicateCheckFields;
    protected $analyzeFalsePositives = false;

    public function __construct()
    {
        $this->config = Plugin::getConfig()->CustomerDuplicatesService->DuplicatesIndex;
        $this->duplicateCheckFields = $this->config->duplicateCheckFields ? $this->config->duplicateCheckFields->toArray() : [];
    }

    public function recreateIndex(LoggerInterface $logger)
    {
        $db = Db::get();
        $db->query("truncate table " . self::DUPLICATES_TABLE);
        $db->query("truncate table " . self::DUPLICATES_CUSTOMERS_TABLE);

        $logger->notice("tables truncated");


        $customerList = Factory::getInstance()->getCustomerProvider()->getList();
        $customerList->setCondition("active = 1");
        $customerList->setOrderKey('o_id');

        $paginator = new \Zend_Paginator($customerList);
        $paginator->setItemCountPerPage(200);

        $totalPages = $paginator->getPages()->pageCount;
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $logger->notice(sprintf("execute page %s of %s", $pageNumber, $totalPages));
            $paginator->setCurrentPageNumber($pageNumber);

            foreach($paginator as $customer) {

                $logger->notice(sprintf("update index for %s", (string) $customer));

                Factory::getInstance()->getCustomerDuplicatesService()->updateDuplicateIndexForCustomer($customer);

            }
        }
    }


    public function updateDuplicateIndexForCustomer(CustomerInterface $customer)
    {
        $duplicateDataRows = [];
        foreach($this->duplicateCheckFields as $fields) {
            $data = [];
            foreach($fields as $field => $options) {
                $getter = 'get' . ucfirst($field);
                $data[$field] = $this->transformDataForDuplicateIndex($customer->$getter(), $field);
            }


            $duplicateDataRows[] = $data;
        }

        $this->updateDuplicateIndex($customer->getId(), $duplicateDataRows, $this->duplicateCheckFields);
    }

    public function calculatePotentialDuplicates()
    {

        if($this->analyzeFalsePositives) {
            $db = Db::get();
            $db->query("truncate table " . self::FALSE_POSITIVES_TABLE);
        }


        $exakt = $this->calculateExaktDuplicateMatches();
        $fuzzy = $this->calculateFuzzyDuplicateMatches();

        $total = [];

        foreach($exakt as $item) {
            sort($item);
            $total[] = implode(',', $item);
        }

        foreach($fuzzy as $item) {
            sort($item);
            $total[] = implode(',', $item);
        }

        $total = array_unique($total);

        $totalIds = [];
        foreach($total as $row) {

            if(!$id = Db::get()->fetchOne("select id from " . self::POTENTIAL_DUPLICATES_TABLE  . " where duplicateIds = '" . $row . "'")) {
                Db::get()->insert(self::POTENTIAL_DUPLICATES_TABLE, [
                    'duplicateIds' => $row,
                    'creationDate' => time()
                ]);

                $id = Db::get()->lastInsertId();
            }

            $totalIds[] = $id;
        }


        Factory::getInstance()->getLogger()->notice("delete potential duplicates which are not valid anymore");
        Db::get()->query("delete from " . self::POTENTIAL_DUPLICATES_TABLE . " where id not in(" . implode(',', $totalIds) . ")");
    }

    /**
     * @return bool
     */
    public function getAnalyzeFalsePositives()
    {
        return $this->analyzeFalsePositives;
    }

    /**
     * @param bool $analyseFalsePositives
     */
    public function setAnalyzeFalsePositives($analyzeFalsePositives)
    {
        $this->analyzeFalsePositives = $analyzeFalsePositives;
    }



    protected function calculateExaktDuplicateMatches()
    {
        $db = Db::get();

        $duplicateIds = $db->fetchCol("select duplicate_id from " . self::DUPLICATES_CUSTOMERS_TABLE . " group by duplicate_id having count(*) > 1 order by count(*) desc");

        $result = [];

        foreach($duplicateIds as $duplicateId) {
            $customerIds = $db->fetchCol("select customer_id from " . self::DUPLICATES_CUSTOMERS_TABLE . " where duplicate_id = " . $duplicateId . " order by customer_id");

            $result[] = $customerIds;
        }

        return $result;
    }

    protected function calculateFuzzyDuplicateMatches()
    {

        $metaphone = $this->calculateFuzzyDuplicateMatchesByAlgorithm("metaphone"); // 5268
        $soundex = $this->calculateFuzzyDuplicateMatchesByAlgorithm("soundex"); //5602


        return array_merge((array) $metaphone, (array) $soundex);
    }

    protected function calculateFuzzyDuplicateMatchesByAlgorithm($algorithm)
    {
        $db = Db::get();

        $phoneticDuplicates = $db->fetchCol("select `" . $algorithm . "` from " . self::DUPLICATES_TABLE . " where `" . $algorithm . "` is not null and `" . $algorithm . "` != '' group by `" . $algorithm . "` having count(*) > 1");

        $result = [];

        foreach($phoneticDuplicates as $phoneticDuplicate) {

            $sql = "select * from plugin_cmf_duplicatesindex_customers c, plugin_cmf_duplicatesindex i where i.id = c.duplicate_id and " . $algorithm . " = '" . $phoneticDuplicate . "'  order by customer_id";

            $rows = $db->fetchAll($sql);

            $customerIdClusters = $this->extractSimilarCustomerIdClusters($rows);

            foreach($customerIdClusters as $cluster) {
                $result[] = $cluster;
            }

        }

        return $result;
    }

    protected function extractSimilarCustomerIdClusters($rows) {

        $groupedByFieldCombination = [];
        foreach($rows as $row) {
            $groupedByFieldCombination[$row['fieldCombination']] = isset($groupedByFieldCombination[$row['fieldCombination']]) ? $groupedByFieldCombination[$row['fieldCombination']] : [];
            $groupedByFieldCombination[$row['fieldCombination']][] = $row;
        }

        $result = [];
        foreach($groupedByFieldCombination as $fieldCombinationRows) {
            $resultCluster = [];

            if(!$this->rowsAreSimilar($fieldCombinationRows)) {
                continue;
            }

            foreach($fieldCombinationRows as $fieldCombinationRow) {
                $resultCluster[] = $fieldCombinationRow['customer_id'];
            }
            sort($resultCluster);
            $resultCluster = array_unique($resultCluster);

            if(sizeof($resultCluster) > 1) {
                $result[] = $resultCluster;
            }
        }

        return $result;
    }

    /**
     * @param array $rows
     *
     * return bool
     */
    protected function rowsAreSimilar(array $rows) {

        if(sizeof($rows) < 2) {
            return false;
        }

        $firstRow = $rows[0];

        unset($rows[0]);

        $fieldCombinationConfig = $this->getFieldCombinationConfig(explode(',', $firstRow['fieldCombination']));

        foreach($rows as $row) {
            if(!$this->rowsAreSimilarHelper($firstRow, $row, $fieldCombinationConfig)) {

                Factory::getInstance()->getLogger()->debug("false positive: " . json_encode($firstRow['duplicateData']) . ' | ' . json_encode($row['duplicateData']));

                if($this->analyzeFalsePositives) {
                    Db::get()->insert(self::FALSE_POSITIVES_TABLE, [
                        "row1" => $firstRow['duplicateData'],
                        "row2" => $row['duplicateData'],
                        "row1Details" => json_encode($firstRow),
                        "row2Details" => json_encode($row),
                    ]);
                }

                return false;
            } else {

                Factory::getInstance()->getLogger()->notice("potential duplicate found: " . $firstRow['duplicate_id'] . ' | ' . $row['duplicate_id']);
            }
        }


        return true;
    }

    protected function rowsAreSimilarHelper(array $row1, array $row2, array $fieldCombinationConfig) {

        // fuzzy matching is only enabled if at least one field has a similitry option configured
        $applies = false;
        foreach($fieldCombinationConfig as $field => $options) {
            if ($options['similarity']) {
                $applies = true;
                break;
            }
        }

        if(!$applies) {
            return false;
        }

        foreach($fieldCombinationConfig as $field => $options) {
            if($options['similarity']) {
                /**
                 * @var DataSimilarityMatcherInterface $similarityMatcher;
                 */
                $similarityMatcher = Factory::getInstance()->createObject($options['similarity'], DataSimilarityMatcherInterface::class);

                $dataRow1 = json_decode($row1['duplicateData'], true);
                $dataRow2 = json_decode($row2['duplicateData'], true);

                $treshold = isset($options['similarityTreshold']) ? $options['similarityTreshold'] : null;

                if(!$similarityMatcher->isSimilar($dataRow1[$field], $dataRow2[$field], $treshold)) {
                    return false;
                }
            }
        }


      /*  if(in_array($row1['duplicate_id'], [1031,1032,1033,1034,1035,1040,1041,1042,1043,1044,1045,1046,1047]) && in_array($row1['duplicate_id'], [1031,1032,1033,1034,1035,1040,1041,1042,1043,1044,1045,1046,1047])) {

          //  var_dump($fieldCombinationConfig);

            $similarityMatcher = new BirthDate();
            $dataRow1 = json_decode($row1['duplicateData'], true);
            $dataRow2 = json_decode($row2['duplicateData'], true);
            var_dump($options);
            var_dump($row1);
            var_dump($row2);

            var_dump($similarityMatcher->calculateSimilarity($dataRow1['birthDate'], $dataRow2['birthDate']));
            var_dump($similarityMatcher->isSimilar($dataRow1['birthDate'], $dataRow2['birthDate']));
            exit;
        }*/


        return true;
    }

    protected function getFieldCombinationConfig(array $fieldCombination) {

        foreach($this->duplicateCheckFields as $fields) {

            if(sizeof($fields) != sizeof($fieldCombination)) {
                continue;
            }

            $matched = true;
            foreach($fieldCombination as $field) {
                $iterationMatched = false;
                foreach($fields as $fieldKey => $trash) {
                    if($fieldKey == $field) {
                        $iterationMatched = true;
                    }
                }

                if(!$iterationMatched) {
                    $matched = false;
                }
            }

            if($matched) {
                return $fields;
            }
        }

        return [];
    }

    protected function updateDuplicateIndex($customerId, array $duplicateDataRows, array $fieldCombinations) {
        $db = Db::get();
        $db->beginTransaction();
        try {

            $db->query("delete from " . self::DUPLICATES_CUSTOMERS_TABLE . " where customer_id = ?", $customerId);

            foreach($duplicateDataRows as $index => $duplicateDataRow) {
                $valid = true;
                foreach($duplicateDataRow as $val) {
                    if(!trim($val)) {
                        $valid = false;
                        break;
                    }
                }
                if(!$valid) {
                    break;
                }

                $data = json_encode($duplicateDataRow);
                $fieldCombination = implode(',', array_keys($fieldCombinations[$index]));

                $dataMd5 = md5($data);
                $fieldCombinationCrc = crc32($fieldCombination);



                if(!$duplicateId = $db->fetchOne("select id from " . self::DUPLICATES_TABLE . " WHERE duplicateDataMd5 = ? and fieldCombinationCrc = ?", [$dataMd5, $fieldCombinationCrc])) {

                    $db->insert(self::DUPLICATES_TABLE, [
                        'duplicateData' => $data,
                        'duplicateDataMd5' => $dataMd5,
                        'fieldCombination' => $fieldCombination,
                        'fieldCombinationCrc' => $fieldCombinationCrc,
                        'soundex' =>  $this->getPhoneticHashData($duplicateDataRow, $fieldCombinations[$index], 'soundex'),
                        'metaphone' => $this->getPhoneticHashData($duplicateDataRow, $fieldCombinations[$index], 'metaphone'),
                        'creationDate' => time(),
                    ]);

                    $duplicateId = $db->lastInsertId();
                }

                $db->insert(self::DUPLICATES_CUSTOMERS_TABLE, [
                    'customer_id' => $customerId,
                    'duplicate_id' => $duplicateId
                ]);


            }

            $db->commit();

        }  catch(\Exception $e) {
            $db->rollBack();
            Logger::error($e->getMessage());
        }
    }

    protected function getPhoneticHashData($customerData, $fieldOptions, $algorithm) {
        $data = [];
        foreach($fieldOptions as $field => $options) {
            if($options[$algorithm]) {
                $data[] = $customerData[$field];
            }
        }

        if(!sizeof($data)) {
            return null;
        }
        foreach($data as $key => $value) {
            if($algorithm == 'soundex') {
                $data[$key] = soundex($value);
            } elseif($algorithm == 'metaphone') {
                $data[$key] = metaphone($value);
            }
        }

        return implode('', $data);
    }


    protected function transformDataForDuplicateIndex($data, $field) {

        if(!$class = $this->config->dataTransformers->{$field}) {
            $class = Standard::class;
        }

        $transformer = Factory::getInstance()->createObject($class, DataTransformerInterface::class);

        return $transformer->transform($data);
    }
}