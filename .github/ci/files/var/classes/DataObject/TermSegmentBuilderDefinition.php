<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- name [input]
- terms [block]
-- term [input]
-- phrases [table]
*/

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\TermSegmentBuilderDefinition\Listing getList()
* @method static \Pimcore\Model\DataObject\TermSegmentBuilderDefinition\Listing|\Pimcore\Model\DataObject\TermSegmentBuilderDefinition|null getByName($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class TermSegmentBuilderDefinition extends \CustomerManagementFrameworkBundle\Model\AbstractTermSegmentBuilderDefinition
{
protected $o_classId = "4";
protected $o_className = "TermSegmentBuilderDefinition";
protected $name;
protected $terms;


/**
* @param array $values
* @return \Pimcore\Model\DataObject\TermSegmentBuilderDefinition
*/
public static function create($values = array()) {
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get name - Name
* @return string|null
*/
public function getName(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("name");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->name;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set name - Name
* @param string|null $name
* @return \Pimcore\Model\DataObject\TermSegmentBuilderDefinition
*/
public function setName(?string $name)
{
	$this->name = $name;

	return $this;
}

/**
* Get terms - Terms
* @return \Pimcore\Model\DataObject\Data\BlockElement[][]
*/
public function getTerms(): ?array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("terms");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("terms")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set terms - Terms
* @param \Pimcore\Model\DataObject\Data\BlockElement[][] $terms
* @return \Pimcore\Model\DataObject\TermSegmentBuilderDefinition
*/
public function setTerms(?array $terms)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Block $fd */
	$fd = $this->getClass()->getFieldDefinition("terms");
	$this->terms = $fd->preSetData($this, $terms);
	return $this;
}

}

