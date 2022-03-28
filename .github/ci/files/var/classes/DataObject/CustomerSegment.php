<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- name [input]
- group [manyToOneRelation]
- reference [input]
- calculated [checkbox]
- useAsTargetGroup [checkbox]
- targetGroup [targetGroup]
*/

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing getList()
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByName($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByGroup($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByReference($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByCalculated($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByUseAsTargetGroup($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegment\Listing|\Pimcore\Model\DataObject\CustomerSegment|null getByTargetGroup($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class CustomerSegment extends \CustomerManagementFrameworkBundle\Model\AbstractCustomerSegment
{
protected $o_classId = "2";
protected $o_className = "CustomerSegment";
protected $name;
protected $group;
protected $reference;
protected $calculated;
protected $useAsTargetGroup;
protected $targetGroup;


/**
* @param array $values
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public static function create($values = array()) {
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get name - Segment name
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
* Set name - Segment name
* @param string|null $name
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setName(?string $name)
{
	$this->name = $name;

	return $this;
}

/**
* Get group - Group
* @return \Pimcore\Model\DataObject\AbstractObject|null
*/
public function getGroup(): ?\Pimcore\Model\Element\AbstractElement
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("group");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("group")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set group - Group
* @param \Pimcore\Model\DataObject\AbstractObject $group
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setGroup(?\Pimcore\Model\Element\AbstractElement $group)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("group");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getGroup();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $group);
	if (!$isEqual) {
		$this->markFieldDirty("group", true);
	}
	$this->group = $fd->preSetData($this, $group);
	return $this;
}

/**
* Get reference - Reference
* @return string|null
*/
public function getReference(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("reference");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->reference;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set reference - Reference
* @param string|null $reference
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setReference(?string $reference)
{
	$this->reference = $reference;

	return $this;
}

/**
* Get calculated - calculated
* @return bool|null
*/
public function getCalculated(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("calculated");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->calculated;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set calculated - calculated
* @param bool|null $calculated
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setCalculated(?bool $calculated)
{
	$this->calculated = $calculated;

	return $this;
}

/**
* Get useAsTargetGroup - Use As Target Group
* @return bool|null
*/
public function getUseAsTargetGroup(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("useAsTargetGroup");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->useAsTargetGroup;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set useAsTargetGroup - Use As Target Group
* @param bool|null $useAsTargetGroup
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setUseAsTargetGroup(?bool $useAsTargetGroup)
{
	$this->useAsTargetGroup = $useAsTargetGroup;

	return $this;
}

/**
* Get targetGroup - Linked TargetGroup
* @return string|null
*/
public function getTargetGroup(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("targetGroup");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->targetGroup;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set targetGroup - Linked TargetGroup
* @param string|null $targetGroup
* @return \Pimcore\Model\DataObject\CustomerSegment
*/
public function setTargetGroup(?string $targetGroup)
{
	$this->targetGroup = $targetGroup;

	return $this;
}

}

