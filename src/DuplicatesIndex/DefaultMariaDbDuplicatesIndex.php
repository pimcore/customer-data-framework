<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\DuplicatesIndex;

use CustomerManagementFrameworkBundle\DataSimilarityMatcher\DataSimilarityMatcherInterface;
use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;
use CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex\Standard;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Db;
use Pimcore\Logger;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultMariaDbDuplicatesIndex implements DuplicatesIndexInterface
{
    use LoggerAware;

    const DUPLICATESINDEX_TABLE = 'plugin_cmf_duplicatesindex';
    const DUPLICATESINDEX_CUSTOMERS_TABLE = 'plugin_cmf_duplicatesindex_customers';
    const POTENTIAL_DUPLICATES_TABLE = 'plugin_cmf_potential_duplicates';
    const FALSE_POSITIVES_TABLE = 'plugin_cmf_duplicates_false_positives';

    /**
     * @var array
     */
    protected $duplicateCheckFields;

    /**
     * @var array
     */
    protected $dataTransformers;

    /**
     * @var bool
     */
    protected $enableDuplicatesIndex;

    /**
     * @var bool
     */
    protected $analyzeFalsePositives = false;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * DefaultMariaDbDuplicatesIndex constructor.
     *
     * @param PaginatorInterface $paginator
     * @param bool $enableDuplicatesIndex
     * @param array $duplicateCheckFields
     * @param array $dataTransformers
     */
    public function __construct(
        PaginatorInterface $paginator,
        $enableDuplicatesIndex = false,
        array $duplicateCheckFields = [],
        array $dataTransformers = []
    ) {
        $this->paginator = $paginator;
        $this->enableDuplicatesIndex = $enableDuplicatesIndex;
        $this->duplicateCheckFields = $duplicateCheckFields;
        $this->dataTransformers = $dataTransformers;
    }

    public function recreateIndex()
    {
        $logger = $this->getLogger();

        $db = Db::get();
        $db->executeQuery('truncate table '.self::DUPLICATESINDEX_TABLE);
        $db->executeQuery('truncate table '.self::DUPLICATESINDEX_CUSTOMERS_TABLE);

        if ($this->analyzeFalsePositives) {
            $db = Db::get();
            $db->executeQuery('truncate table '.self::FALSE_POSITIVES_TABLE);
        }

        $logger->notice('tables truncated');

        $customerProvider = \Pimcore::getContainer()->get('cmf.customer_provider');
        $customerList = $customerProvider->getList();

        $customerProvider->addActiveCondition($customerList);
        $customerList->setOrderKey(Service::getVersionDependentDatabaseColumnName('id'));

        $paginator = $this->paginator->paginate($customerList);
        $paginator->setItemNumberPerPage(200);

        $totalPages = $paginator->getPaginationData()['pageCount'];
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            $logger->notice(sprintf('execute page %s of %s', $pageNumber, $totalPages));
            $paginator = $this->paginator->paginate($customerList, $pageNumber, 200);

            foreach ($paginator as $customer) {
                $logger->notice(sprintf('update index for %s', (string)$customer));

                $this->updateDuplicateIndexForCustomer($customer, true);
            }

            \Pimcore::collectGarbage();
        }
    }

    public function updateDuplicateIndexForCustomer(CustomerInterface $customer, $force = false)
    {
        if (!$force && !$this->enableDuplicatesIndex) {
            $this->getLogger()->debug('duplicate index disabled');

            return;
        }

        if (!$this->isRelevantForDuplicateIndex($customer)) {
            $this->deleteCustomerFromDuplicateIndex($customer);

            return;
        }

        $duplicateDataRows = [];
        foreach ($this->duplicateCheckFields as $fields) {
            $data = [];
            foreach ($fields as $field => $options) {
                $getter = 'get'.ucfirst($field);
                $data[$field] = $this->transformDataForDuplicateIndex($customer->$getter(), $field);
            }

            $duplicateDataRows[] = $data;
        }

        $this->updateDuplicateIndex($customer->getId(), $duplicateDataRows, $this->duplicateCheckFields);
    }

    public function isRelevantForDuplicateIndex(CustomerInterface $customer)
    {
        return $customer->getPublished() && $customer->getActive();
    }

    public function deleteCustomerFromDuplicateIndex(CustomerInterface $customer)
    {
        if (!$this->enableDuplicatesIndex) {
            $this->getLogger()->debug('duplicate index disabled');

            return;
        }

        $db = Db::get();
        $db->executeQuery(
            sprintf('delete from %s where customer_id = ?', self::DUPLICATESINDEX_CUSTOMERS_TABLE),
            [$customer->getId()]
        );
        $db->executeQuery(
            sprintf('delete from %s where FIND_IN_SET(?, duplicateCustomerIds)', self::POTENTIAL_DUPLICATES_TABLE),
            [$customer->getId()]
        );
    }

    public function calculatePotentialDuplicates(OutputInterface $output)
    {
        if ($this->analyzeFalsePositives) {
            $db = Db::get();
            $db->executeQuery('truncate table '.self::FALSE_POSITIVES_TABLE);
        }

        $this->cleanupDuplicatesIndex();

        $this->getLogger()->notice('start calculating exact duplicate matches');
        $exakt = $this->calculateExactDuplicateMatches();

        $this->getLogger()->notice('start calculating fuzzy duplicate matches');
        $fuzzy = $this->calculateFuzzyDuplicateMatches($output);

        $total = [];

        foreach ([$exakt, $fuzzy] as $dataSet) {
            foreach ($dataSet as $fieldCombination => $items) {
                foreach ($items as $item) {
                    $item = is_array($item) ? implode(',', $item) : $item;

                    $total[$item] = isset($total[$item]) ? $total[$item] : [];
                    $total[$item][] = $fieldCombination;
                }
            }
        }

        $this->getLogger()->notice('update potential duplicates table');

        $totalIds = [-1];
        foreach ($total as $duplicateIds => $fieldCombinations) {
            if (!$id = Db::get()->fetchOne(
                'select id from '.self::POTENTIAL_DUPLICATES_TABLE.' where duplicateCustomerIds = ?',
                [$duplicateIds]
            )
            ) {
                Db::get()->insert(
                    self::POTENTIAL_DUPLICATES_TABLE,
                    [
                        'duplicateCustomerIds' => $duplicateIds,
                        'fieldCombinations' => implode(';', array_unique((array)$fieldCombinations)),
                        'creationDate' => time(),
                    ]
                );

                $id = Db::get()->lastInsertId();
            }

            $totalIds[] = (int) $id;
        }

        $this->getLogger()->notice('delete potential duplicates which are not valid anymore');
        Db::get()->executeQuery(
            'delete from '.self::POTENTIAL_DUPLICATES_TABLE.' where id not in('.implode(',', $totalIds).')'
        );
    }

    public function getPotentialDuplicates($page, $pageSize = 100, $declined = false, Concrete $filterCustomerList = null)
    {
        $db = \Pimcore\Db::get();

        $select = $db->createQueryBuilder();

        $select
            ->from(self::POTENTIAL_DUPLICATES_TABLE)
            ->select(
                self::POTENTIAL_DUPLICATES_TABLE . '.id',
                'duplicateCustomerIds',
                'declined',
                'fieldCombinations',
                self::POTENTIAL_DUPLICATES_TABLE . '.creationDate',
                self::POTENTIAL_DUPLICATES_TABLE . '.modificationDate'
            )
            ->addOrderBy('id', 'asc');

        if (!is_null($filterCustomerList)) {
            $query = $filterCustomerList->getQueryBuilder()
                ->resetQueryPart('select')
                ->select('id');
            $joinTable = 'object_' . $filterCustomerList->getClassId();
            $joinIdField = $joinTable . '.id';

            $select
                ->distinct()
                ->innerJoin(self::POTENTIAL_DUPLICATES_TABLE, $joinTable, $joinTable, 'FIND_IN_SET(' . $joinIdField . ', duplicateCustomerIds)')
                ->andWhere($joinIdField . ' in (' . $query . ')');
        }

        if ($declined) {
            $select->andWhere('(declined = 1)');
        } else {
            $select->andWhere('(declined is null or declined = 0)');
        }

        $paginator = $this->paginator->paginate($select, $page ?: 0, $pageSize);

        $items = $paginator->getItems();
        foreach ($items as &$row) {

            /**
             * @var \CustomerManagementFrameworkBundle\Model\CustomerDuplicates\PotentialDuplicateItemInterface $item
             */
            $item = \Pimcore::getContainer()->get('cmf.potential_duplicate_item');

            $customers = [];
            foreach (explode(',', $row['duplicateCustomerIds']) as $customerId) {
                if ($customer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($customerId)) {
                    $customers[] = $customer;
                }
            }

            if (sizeof($customers) != 2) {
                throw new \Exception(sprintf('Invalid duplicate customer combination. A combination needs to consist of 2 customers. %s customer(s) found. Customer IDs: %s', sizeof($customers), $row['duplicateCustomerIds']));
            }

            $fieldCombinations = [];
            foreach (explode(';', $row['fieldCombinations']) as $fieldCombination) {
                $fieldCombinations[] = explode(',', $fieldCombination);
            }

            $item->setId($row['id']);
            $item->setDuplicateCustomers($customers);
            $item->setCreationDate($row['creationDate']);
            $item->setModificationDate($row['modificationDate']);
            $item->setDeclined((bool)$row['declined']);
            $item->setFieldCombinations($fieldCombinations);

            $row = $item;
        }
        $paginator->setItems($items);

        return $paginator;
    }

    public function getFalsePositives($page, $pageSize = 200)
    {
        $db = \Pimcore\Db::get();

        $select = $db->createQueryBuilder();
        $select
            ->from(self::FALSE_POSITIVES_TABLE)
            ->select('row1',
                    'row2',
                    'row1Details',
                    'row2Details'
            )
            ->addOrderBy('row1', 'asc');

        $paginator = $this->paginator->paginate($select, $page ?: 0, $pageSize);

        return $paginator;
    }

    /**
     * @return bool
     */
    public function getAnalyzeFalsePositives()
    {
        return $this->analyzeFalsePositives;
    }

    /**
     * @param bool $analyzeFalsePositives
     */
    public function setAnalyzeFalsePositives($analyzeFalsePositives)
    {
        $this->analyzeFalsePositives = $analyzeFalsePositives;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function declinePotentialDuplicate($id)
    {
        $db = Db::get();
        $db->executeQuery(sprintf('update %s set declined = 1 where id = ?', self::POTENTIAL_DUPLICATES_TABLE), [$id]);
    }

    protected function cleanupDuplicatesIndex()
    {
        $this->getLogger()->notice('cleanup duplicates index');
        $db = Db::get();

        $db->executeQuery(
            sprintf(
                'delete from %s where id not in(select distinct duplicate_id from %s )',
                self::DUPLICATESINDEX_TABLE,
                self::DUPLICATESINDEX_CUSTOMERS_TABLE
            )
        );
    }

    protected function calculateExactDuplicateMatches()
    {
        $db = Db::get();

        $duplicateIds = $db->fetchFirstColumn(
            'select duplicate_id from '.self::DUPLICATESINDEX_CUSTOMERS_TABLE.' group by duplicate_id having count(*) > 1 order by count(*) desc'
        );

        $result = [];

        foreach ($duplicateIds as $duplicateId) {
            $customerIds = $db->fetchFirstColumn(
                'select customer_id from '.self::DUPLICATESINDEX_CUSTOMERS_TABLE.' where duplicate_id = ? order by customer_id',
                [$duplicateId]
            );
            $fieldCombination = $db->fetchOne(
                'select fieldCombination from '.self::DUPLICATESINDEX_TABLE.' where id = ?',
                [$duplicateId]
            );

            $clusters = $this->getCombinations($customerIds, 2);

            foreach ($clusters as $cluster) {
                $result[$fieldCombination][] = $cluster;
            }
        }

        return $result;
    }

    protected function calculateFuzzyDuplicateMatches(OutputInterface $output)
    {
        $metaphone = $this->calculateFuzzyDuplicateMatchesByAlgorithm('metaphone', $output);
        $soundex = $this->calculateFuzzyDuplicateMatchesByAlgorithm('soundex', $output);

        $resultSets = [$metaphone, $soundex];

        $result = [];
        foreach ($resultSets as $resultSet) {
            foreach ($resultSet as $fieldCombination => $duplicateClusters) {
                $result[$fieldCombination] = isset($result[$fieldCombination]) ? $result[$fieldCombination] : [];

                $result[$fieldCombination] = array_merge((array)$result[$fieldCombination], $duplicateClusters);
            }
        }

        return $result;
    }

    protected function calculateFuzzyDuplicateMatchesByAlgorithm($algorithm, OutputInterface $output)
    {
        $db = Db::get();

        $phoneticDuplicates = $db->fetchFirstColumn(
            'select `'.$algorithm.'` from '.self::DUPLICATESINDEX_TABLE.' where `'.$algorithm.'` is not null and `'.$algorithm."` != '' group by `".$algorithm.'` having count(*) > 1'
        );

        $result = [];

        $totalCount = sizeof($phoneticDuplicates);

        $output->writeln('');
        $this->getLogger()->notice(sprintf('calculate potential duplicates for %s', $algorithm));

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat('verbose');

        foreach ($phoneticDuplicates as $phoneticDuplicate) {
            $progress->advance();

            $rows = $db->fetchAllAssociative(
                'select * from '.self::DUPLICATESINDEX_CUSTOMERS_TABLE.' c, '.self::DUPLICATESINDEX_TABLE.' i where i.id = c.duplicate_id and `'.$algorithm.'` = ?  order by customer_id',
                [$phoneticDuplicate]
            );

            $customerIdClusters = $this->extractSimilarCustomerIdClustersGroupedByFieldCombinations($rows);

            foreach ($customerIdClusters as $fieldCombination => $clusters) {
                foreach ($clusters as $cluster) {
                    $result[$fieldCombination][] = $cluster;
                }
            }
        }

        $progress->finish();
        $output->writeln('');
        $output->writeln('');

        return $result;
    }

    protected function extractSimilarCustomerIdClustersGroupedByFieldCombinations($rows)
    {
        $groupedByFieldCombination = [];
        foreach ($rows as $row) {
            $groupedByFieldCombination[$row['fieldCombination']] = isset($groupedByFieldCombination[$row['fieldCombination']]) ? $groupedByFieldCombination[$row['fieldCombination']] : [];
            $groupedByFieldCombination[$row['fieldCombination']][] = $row;
        }

        $result = [];
        foreach ($groupedByFieldCombination as $fieldCombination => $fieldCombinationRows) {
            $result[$fieldCombination] = $this->extractSimilarCustomerIdClusters($fieldCombinationRows);
        }

        return $result;
    }

    private function extractSimilarCustomerIdClusters($rows)
    {
        $result = [];

        foreach ($this->getCombinations($rows, 2) as $combination) {
            if ($this->rowsAreSimilar($combination)) {
                $cluster = [];
                foreach ($combination as $row) {
                    $cluster[] = $row['customer_id'];
                }
                $result[] = $cluster;
            }
        }

        return $result;
    }

    protected function getCombinations($base, $n)
    {
        $baselen = count($base);
        if ($baselen == 0) {
            return;
        }
        if ($n == 1) {
            $return = [];
            foreach ($base as $b) {
                $return[] = [$b];
            }

            return $return;
        } else {
            //get one level lower combinations
            $oneLevelLower = $this->getCombinations($base, $n - 1);

            //for every one level lower combinations add one element to them that the last element of a combination is preceeded by the element which follows it in base array if there is none, does not add
            $newCombs = [];

            foreach ($oneLevelLower as $oll) {
                $lastEl = $oll[$n - 2];
                $found = false;
                foreach ($base as $key => $b) {
                    if ($b == $lastEl) {
                        $found = true;
                        continue;
                        //last element found
                    }
                    if ($found == true) {
                        //add to combinations with last element
                        if ($key < $baselen) {
                            $tmp = $oll;
                            $newCombination = array_slice($tmp, 0);
                            $newCombination[] = $b;
                            $newCombs[] = array_slice($newCombination, 0);
                        }
                    }
                }
            }
        }

        return $newCombs;
    }

    /**
     * @param array $rows
     *
     * return bool
     */
    protected function rowsAreSimilar(array $rows)
    {
        if (sizeof($rows) < 2) {
            return false;
        }

        $firstRow = $rows[0];

        unset($rows[0]);

        $fieldCombinationConfig = $this->getFieldCombinationConfig($firstRow['fieldCombination']);

        foreach ($rows as $row) {
            if (!$this->twoRowsAreSimilar($firstRow, $row, $fieldCombinationConfig)) {
                $this->getLogger()->debug(
                    'false positive: '.json_encode($firstRow['duplicateData']).' | '.json_encode($row['duplicateData'])
                );

                if ($this->analyzeFalsePositives) {
                    Db::get()->insert(
                        self::FALSE_POSITIVES_TABLE,
                        [
                            'row1' => $firstRow['duplicateData'],
                            'row2' => $row['duplicateData'],
                            'row1Details' => json_encode($firstRow),
                            'row2Details' => json_encode($row),
                        ]
                    );
                }

                return false;
            } else {
                $this->getLogger()->debug(
                    'potential duplicate found: '.$firstRow['duplicate_id'].' | '.$row['duplicate_id']
                );
            }
        }

        return true;
    }

    protected function twoRowsAreSimilar(array $row1, array $row2, array $fieldCombinationConfig)
    {

        // fuzzy matching is only enabled if at least one field has a similitry option configured
        $applies = false;
        foreach ($fieldCombinationConfig as $field => $options) {
            if ($options['similarity']) {
                $applies = true;
                break;
            }
        }

        if (!$applies) {
            return false;
        }

        foreach ($fieldCombinationConfig as $field => $options) {
            if ($options['similarity']) {
                $similarityMatcher = $this->getSimilarityMatcher($options['similarity']);

                $dataRow1 = json_decode($row1['duplicateData'], true);
                $dataRow2 = json_decode($row2['duplicateData'], true);

                $threshold = isset($options['similarityThreshold']) ? $options['similarityThreshold'] : null;

                if (!$similarityMatcher->isSimilar($dataRow1[$field], $dataRow2[$field], $threshold)) {
                    return false;
                }
            }
        }

        return true;
    }

    private $similarityMatchers = [];

    /**
     * @param string $similiarity
     *
     * @return DataSimilarityMatcherInterface
     */
    protected function getSimilarityMatcher($similiarity)
    {
        if (!isset($this->similarityMatchers[$similiarity])) {
            $this->similarityMatchers[$similiarity] = Factory::getInstance()->createObject(
                $similiarity,
                DataSimilarityMatcherInterface::class
            );
        }

        return $this->similarityMatchers[$similiarity];
    }

    private $fieldCombinationConfig = [];

    /**
     * @param string $fieldCombinationCommaSeparated
     *
     * @return array
     */
    protected function getFieldCombinationConfig($fieldCombinationCommaSeparated)
    {
        if (!isset($this->fieldCombinationConfig[$fieldCombinationCommaSeparated])) {
            $this->fieldCombinationConfig[$fieldCombinationCommaSeparated] = [];
            foreach ($this->duplicateCheckFields as $fields) {
                $fieldCombination = explode(',', $fieldCombinationCommaSeparated);

                if (sizeof($fields) != sizeof($fieldCombination)) {
                    continue;
                }

                $matched = true;
                foreach ($fieldCombination as $field) {
                    $iterationMatched = false;
                    foreach ($fields as $fieldKey => $trash) {
                        if ($fieldKey == $field) {
                            $iterationMatched = true;
                        }
                    }

                    if (!$iterationMatched) {
                        $matched = false;
                    }
                }

                if ($matched) {
                    $this->fieldCombinationConfig[$fieldCombinationCommaSeparated] = $fields;
                    break;
                }
            }
        }

        return $this->fieldCombinationConfig[$fieldCombinationCommaSeparated];
    }

    protected function updateDuplicateIndex($customerId, array $duplicateDataRows, array $fieldCombinations)
    {
        $db = Db::get();
        $db->beginTransaction();
        try {
            $db->executeQuery('delete from '.self::DUPLICATESINDEX_CUSTOMERS_TABLE.' where customer_id = ?', [$customerId]);

            foreach ($duplicateDataRows as $index => $duplicateDataRow) {
                $valid = true;
                foreach ($duplicateDataRow as $val) {
                    if (!trim($val)) {
                        $valid = false;
                        break;
                    }
                }

                if (!$valid) {
                    continue;
                }

                $data = json_encode($duplicateDataRow);
                $fieldCombination = implode(',', array_keys($fieldCombinations[$index]));

                $dataMd5 = md5($data);
                $fieldCombinationCrc = crc32($fieldCombination);

                if (!$duplicateId = $db->fetchOne(
                    'select id from '.self::DUPLICATESINDEX_TABLE.' WHERE duplicateDataMd5 = ? and fieldCombinationCrc = ?',
                    [$dataMd5, $fieldCombinationCrc]
                )
                ) {
                    $db->insert(
                        self::DUPLICATESINDEX_TABLE,
                        [
                            'duplicateData' => $data,
                            'duplicateDataMd5' => $dataMd5,
                            'fieldCombination' => $fieldCombination,
                            'fieldCombinationCrc' => $fieldCombinationCrc,
                            'soundex' => $this->getPhoneticHashData(
                                $duplicateDataRow,
                                $fieldCombinations[$index],
                                'soundex'
                            ),
                            'metaphone' => $this->getPhoneticHashData(
                                $duplicateDataRow,
                                $fieldCombinations[$index],
                                'metaphone'
                            ),
                            'creationDate' => time(),
                        ]
                    );

                    $duplicateId = $db->lastInsertId();
                }

                $db->insert(
                    self::DUPLICATESINDEX_CUSTOMERS_TABLE,
                    [
                        'customer_id' => $customerId,
                        'duplicate_id' => $duplicateId,
                    ]
                );
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            Logger::error($e->getMessage());
        }
    }

    protected function getPhoneticHashData($customerData, $fieldOptions, $algorithm)
    {
        $data = [];
        foreach ($fieldOptions as $field => $options) {
            if ($options[$algorithm]) {
                $data[] = $customerData[$field];
            }
        }

        if (!sizeof($data)) {
            return null;
        }
        foreach ($data as $key => $value) {
            if ($algorithm == 'soundex') {
                $data[$key] = soundex($value);
            } elseif ($algorithm == 'metaphone') {
                $data[$key] = metaphone($value);
            }
        }

        return implode('', $data);
    }

    protected function transformDataForDuplicateIndex($data, $field)
    {
        $class = $this->dataTransformers[$field] ?? false;

        if (!$class) {
            $class = Standard::class;
        }

        $transformer = Factory::getInstance()->createObject($class, DataTransformerInterface::class);

        return $transformer->transform($data);
    }
}
