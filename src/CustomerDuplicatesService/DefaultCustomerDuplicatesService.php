<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 16:14
 */

namespace CustomerManagementFrameworkBundle\CustomerDuplicatesService;

use Carbon\Carbon;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Listing\Concrete;

class DefaultCustomerDuplicatesService implements CustomerDuplicatesServiceInterface
{
    private $config;

    /**
     * @var array
     */
    private $duplicateCheckFields;

    /**
     * @var array
     */
    protected $matchedDuplicateFields;

    public function __construct()
    {
        $this->config = Config::getConfig()->CustomerDuplicatesService;
        $this->duplicateCheckFields = $this->config->duplicateCheckFields ? $this->config->duplicateCheckFields->toArray(
        ) : [];
    }

    /**
     * Returns a list of duplicates for the given customer.
     * Which fields should be used for matching duplicates could be defined in the config of the CMF plugin.
     *
     * @param CustomerInterface $customer
     * @param int $limit
     *
     * @return \Pimcore\Model\Object\Listing\Concrete|null
     */
    public function getDuplicatesOfCustomer(CustomerInterface $customer, $limit = 0)
    {
        foreach ($this->duplicateCheckFields as $fields) {
            if (!is_array($fields)) {
                return $this->getDuplicatesOfCustomerByFields($customer, $this->duplicateCheckFields, $limit);
            }

            $duplicates = $this->getDuplicatesOfCustomerByFields($customer, $fields, $limit);

            if (!is_null($duplicates) && $duplicates->getCount()) {
                return $duplicates;
            }
        }

        return null;
    }

    /**
     * Returns a list of duplicates/customers which are matching the given data.
     * Which fields should be used for matching duplicates could be defined in the config of the CMF plugin.
     *
     * @param array $data
     * @param int $limit
     *
     * @return \Pimcore\Model\Object\Listing\Concrete|null
     */
    public function getDuplicatesByData(array $data, $limit = 0)
    {
        if (!sizeof($data)) {
            return null;
        }

        $list = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();

        $list
            ->addConditionParam('o_published = ?', 1)
            ->addConditionParam('active = ?', 1);

        foreach ($data as $field => $value) {
            if (is_null($value)) {
                return null;
            } else {
                $this->addNormalizedMysqlCompareCondition($list, $field, $value);
            }
        }

        if ($limit) {
            $list->setLimit($limit);
        }

        return $list;
    }

    /**
     * Returns a list of duplicates for the given customer. Duplicates are matched by the fields given in $fields.
     *
     * @param array $data
     * @param int $limit
     *
     * @return \Pimcore\Model\Object\Listing\Concrete|null
     */
    public function getDuplicatesOfCustomerByFields(CustomerInterface $customer, array $fields, $limit = 0)
    {
        if (!sizeof($fields)) {
            return null;
        }

        $data = [];
        foreach ($fields as $field) {
            $getter = 'get'.ucfirst($field);
            $value = $customer->$getter();

            if (is_null($value)) {
                return null;
            } else {
                $data[$field] = $value;
            }
        }

        $duplicates = $this->getDuplicatesByData($data, $limit);

        if ($customer->getId()) {
            $duplicates->addConditionParam('o_id != ?', $customer->getId());
        }

        if (!is_null($duplicates) && $duplicates->getCount()) {
            $this->matchedDuplicateFields = $fields;
        }

        return $duplicates;
    }

    /**
     * Returns which field combination matched the last found duplicates.
     *
     * @return array
     */
    public function getMatchedDuplicateFields()
    {
        return $this->matchedDuplicateFields;
    }

    /**
     * Update the duplicate index for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function updateDuplicateIndexForCustomer(CustomerInterface $customer)
    {
        if ((bool)$this->config->DuplicatesIndex->enableDuplicatesIndex === false) {
            return;
        }

        $duplicatesIndex = \Pimcore::getContainer()->get('cmf.customer_duplicates_index');
        $duplicatesIndex->updateDuplicateIndexForCustomer($customer);
    }

    /**
     * @param Concrete $list
     * @param $field
     * @param $value
     *
     * @return void;
     */
    protected function addNormalizedMysqlCompareCondition(Concrete &$list, $field, $value)
    {
        $class = ClassDefinition::getByName(Config::getConfig()->General->CustomerPimcoreClass);
        $fd = $class->getFieldDefinition($field);

        if (!$fd) {
            return;
        }

        if ($value instanceof ElementInterface) {
            $this->addNormalizedMysqlCompareConditionForSingleRelationFields($list, $field, $value);

            return;
        }

        if (is_array($value) && ($value[0] instanceof ElementInterface)) {
            $this->addNormalizedMysqlCompareConditionForMultiRelationFields($list, $field, $value);

            return;
        }

        if (strpos($fd->getColumnType(), 'char') == !false) {
            $this->addNormalizedMysqlCompareConditionForStringFields($list, $field, $value);

            return;
        }

        if ($value instanceof Carbon || $value instanceof \Pimcore\Date || $value instanceof \DateTime) {
            $this->addNormalizedMysqlCompareConditionForDateFields($list, $field, $value);

            return;
        }

        $type = gettype($value) == 'object' ? get_class($value) : gettype($value);
        throw new \Exception(
            sprintf('duplicate check for type of field %s not implemented (type of value: %s)', $field, $type)
        );
    }

    /**
     * @param Concrete $list
     * @param string $field
     * @param string $value
     */
    protected function addNormalizedMysqlCompareConditionForStringFields(Concrete &$list, $field, $value)
    {
        $list->addConditionParam('TRIM(LCASE('.$field.')) = ?', trim(mb_strtolower($value, 'UTF-8')));
    }

    /**
     * @param Concrete $list
     * @param string $field
     * @param Carbon|\Pimcore\Date|\DateTime $value
     */
    protected function addNormalizedMysqlCompareConditionForDateFields(Concrete &$list, $field, $value)
    {
        $list->addConditionParam($field.' = ?', $value->getTimestamp());
    }

    /**
     * @param Concrete $list
     * @param string $field
     * @param ElementInterface $value
     */
    protected function addNormalizedMysqlCompareConditionForSingleRelationFields(Concrete &$list, $field, $value)
    {
        $list->addConditionParam($field.'__id = ?', $value->getId());
    }

    /**
     * @param Concrete $list
     * @param string $field
     * @param ElementInterface[] $value
     */
    protected function addNormalizedMysqlCompareConditionForMultiRelationFields(Concrete &$list, $field, $value)
    {
        $ids = [];
        foreach ($value as $row) {
            $ids[] = $row->getId();
        }

        $list->addConditionParam($field.' = ?', implode(',', $ids).',');
    }
}
