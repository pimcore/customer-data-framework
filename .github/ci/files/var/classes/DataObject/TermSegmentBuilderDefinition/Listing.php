<?php

namespace Pimcore\Model\DataObject\TermSegmentBuilderDefinition;

use Pimcore\Model\DataObject;

/**
 * @method DataObject\TermSegmentBuilderDefinition|false current()
 * @method DataObject\TermSegmentBuilderDefinition[] load()
 * @method DataObject\TermSegmentBuilderDefinition[] getData()
 */

class Listing extends DataObject\Listing\Concrete
{
protected $classId = "4";
protected $className = "TermSegmentBuilderDefinition";


/**
* Filter by name (Name)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return static
*/
public function filterByName ($data, $operator = '=')
{
	$this->getClass()->getFieldDefinition("name")->addListingFilter($this, $data, $operator);
	return $this;
}



}
