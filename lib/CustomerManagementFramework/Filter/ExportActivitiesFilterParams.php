<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 25.10.2016
 * Time: 09:41
 */

namespace CustomerManagementFramework\Filter;

class ExportActivitiesFilterParams {

    /**
     * @var string|boolean
     */
    private $type;

    /**
     * @var int
     */
    private $modifiedSinceTimestamp;

    /**
     * @var array
     */
    private $allParams;

    /**
     * @return string|boolean
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type|boolean
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getModifiedSinceTimestamp()
    {
        return $this->modifiedSinceTimestamp;
    }

    /**
     * @param int $modifiedSinceTimestamp
     */
    public function setModifiedSinceTimestamp($modifiedSinceTimestamp)
    {
        $this->modifiedSinceTimestamp = $modifiedSinceTimestamp;
    }

    /**
     * @return array
     */
    public function getAllParams()
    {
        return $this->allParams;
    }

    /**
     * @param array $allParams
     */
    public function setAllParams($allParams)
    {
        $this->allParams = $allParams;
    }


}