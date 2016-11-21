<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 16:14
 */

namespace CustomerManagementFramework\CustomerDuplicatesService;

use Carbon\Carbon;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Object\ClassDefinition;
use \Pimcore\Model\Object\Customer;

class DefaultCustomerDuplicatesService implements CustomerDuplicatesServiceInterface{

    private $config;

    /**
     * @var array
     */
    private $duplicateCheckFields;

    public function __construct()
    {
        $this->config =  Plugin::getConfig()->CustomerDuplicatesService;
        $this->duplicateCheckFields = $this->config->duplicateCheckFields ? $this->config->duplicateCheckFields->toArray() : [];
    }

    public function getDuplicatesOfCustomer(CustomerInterface $customer) {
        foreach($this->duplicateCheckFields as $fields) {

            if(!is_array($fields)) {
                return $this->getDuplicatesOfCustomerByFields($customer, $this->duplicateCheckFields);
            }

            if($duplicates = $this->getDuplicatesOfCustomerByFields($customer, $fields)) {
                return $duplicates;
            }
        }

        return [];
    }

    protected function getDuplicatesOfCustomerByFields(CustomerInterface $customer, array $fields) {


        if(!sizeof($fields)) {
            return [];
        }

        $list = new Customer\Listing;

        $conditions = ["o_id !=" . $customer->getId()];
        foreach($fields as $field) {
            $getter = 'get' . ucfirst($field);
            $value = $customer->$getter();

            if(is_null($value)) {
                $conditions[] = $field . " is null";
            } else {
                $conditions[] = $this->normalizeMysqlFieldname($field) . " = " . $list->quote($this->normalizeMysqlCompareValue($field, $value));
            }
        }

        $conditions = '(' . implode(' and ', $conditions) . ')';

        $list->setCondition($conditions);
        $list = $list->load();

        return $list ? : [];
    }

    protected function normalizeMysqlFieldname($fieldName) {

        $class = ClassDefinition::getByName(Plugin::getConfig()->General->CustomerPimcoreClass);
        $fd = $class->getFieldDefinition($fieldName);

        // string fields
        if(strpos($fd->getColumnType(), 'char') ==! false) {
            return sprintf("TRIM(LCASE(%s))", $fieldName);
        }

        return $fieldName;
    }

    protected function normalizeMysqlCompareValue($fieldName, $value) {

        $class = ClassDefinition::getByName(Plugin::getConfig()->General->CustomerPimcoreClass);
        $fd = $class->getFieldDefinition($fieldName);

        //string fields
        if(strpos($fd->getColumnType(), 'char') ==! false) {
            return trim(strtolower($value));
        }

        //date / date+time fields
        if($value instanceof Carbon) {
            return $value->getTimestamp();
        }

        return $value;
    }
}