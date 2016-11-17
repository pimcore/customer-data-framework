<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\DataTransformer\AttributeDataTransformer;


interface AttributeDataTransformerInterface
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function transform($data);
}