<?php

/**
* Inheritance: no
* Variants: no


Fields Summary:
- name [input]
- reference [input]
- calculated [checkbox]
- showAsFilter [checkbox]
- filterSortOrder [numeric]
- exportNewsletterProvider [checkbox]
*/

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing getList()
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByName($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByReference($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByCalculated($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByShowAsFilter($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByFilterSortOrder($value, $limit = 0, $offset = 0, $objectTypes = null)
* @method static \Pimcore\Model\DataObject\CustomerSegmentGroup\Listing|\Pimcore\Model\DataObject\CustomerSegmentGroup|null getByExportNewsletterProvider($value, $limit = 0, $offset = 0, $objectTypes = null)
*/

class CustomerSegmentGroup extends Concrete
{
protected $o_classId = "1";
protected $o_className = "CustomerSegmentGroup";
protected $name;
protected $reference;
protected $calculated;
protected $showAsFilter;
protected $filterSortOrder;
protected $exportNewsletterProvider;


/**
* @param array $values
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public static function create($values = array()) {
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get name - Segment group name
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
* Set name - Segment group name
* @param string|null $name
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public function setName(?string $name)
{
	$this->name = $name;

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
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
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
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public function setCalculated(?bool $calculated)
{
	$this->calculated = $calculated;

	return $this;
}

/**
* Get showAsFilter - Show as Filter
* @return bool|null
*/
public function getShowAsFilter(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("showAsFilter");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->showAsFilter;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set showAsFilter - Show as Filter
* @param bool|null $showAsFilter
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public function setShowAsFilter(?bool $showAsFilter)
{
	$this->showAsFilter = $showAsFilter;

	return $this;
}

/**
* Get filterSortOrder - Filter sort order
* @return int|null
*/
public function getFilterSortOrder(): ?int
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("filterSortOrder");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->filterSortOrder;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set filterSortOrder - Filter sort order
* @param int|null $filterSortOrder
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public function setFilterSortOrder(?int $filterSortOrder)
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getClass()->getFieldDefinition("filterSortOrder");
	$this->filterSortOrder = $fd->preSetData($this, $filterSortOrder);
	return $this;
}

/**
* Get exportNewsletterProvider - Export to newsletter provider
* @return bool|null
*/
public function getExportNewsletterProvider(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("exportNewsletterProvider");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->exportNewsletterProvider;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set exportNewsletterProvider - Export to newsletter provider
* @param bool|null $exportNewsletterProvider
* @return \Pimcore\Model\DataObject\CustomerSegmentGroup
*/
public function setExportNewsletterProvider(?bool $exportNewsletterProvider)
{
	$this->exportNewsletterProvider = $exportNewsletterProvider;

	return $this;
}

}

