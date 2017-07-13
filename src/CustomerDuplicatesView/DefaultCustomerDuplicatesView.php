<?php

namespace CustomerManagementFrameworkBundle\CustomerDuplicatesView;


use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Plugin;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\Object\ClassDefinition;


class DefaultCustomerDuplicatesView implements CustomerDuplicatesViewInterface
{
    /**
     * @var ViewFormatterInterface
     */
    protected $viewFormatter;

    /**
     * @param ViewFormatterInterface $viewFormatter
     */
    public function __construct(ViewFormatterInterface $viewFormatter)
    {
        $this->viewFormatter = $viewFormatter;
    }

    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter()
    {
        return $this->viewFormatter;
    }

    public function getListData(CustomerInterface $customer)
    {
        if (!sizeof(Config::getConfig()->CustomerDuplicatesService->DuplicatesView->listFields)) {
            throw new \Exception(
                "CustomerDuplicatesService->DuplicatesView->listFields not defined in CMF config file"
            );
        }

        $class = ClassDefinition::getById($customer::classId());

        $fields = Config::getConfig()->CustomerDuplicatesService->DuplicatesView->listFields->toArray();
        $listData = [];

        foreach ($fields as $field) {

            if (is_array($field)) {
                $labels = [];
                $values = [];
                foreach ($field as $_field) {
                    $labels[] = $this->viewFormatter->getLabelByFieldName($class, $_field);
                    $values[] = $this->getValueFromCustomer($customer, $_field);
                }
                $listData[implode('/', $labels)] = implode(' ', $values);
            } else {
                $listData[$this->viewFormatter->getLabelByFieldName($class, $field)] = $this->getValueFromCustomer(
                    $customer,
                    $field
                );
            }
        }

        return $listData;
    }

    protected function getValueFromCustomer(CustomerInterface $customer, $fieldName, $format = true)
    {
        if ($fieldName == 'id') {
            return $customer->getId();
        }

        $getter = 'get'.ucfirst($fieldName);
        $value = $customer->$getter();

        if ($format) {
            $class = ClassDefinition::getById($customer::classId());
            $fd = $class->getFieldDefinition($fieldName);
            $value = $this->viewFormatter->formatValueByFieldDefinition($fd, $customer->$getter());
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function translate($messageId, $parameters = [])
    {
        return $this->viewFormatter->translate($messageId, $parameters);
    }
}
