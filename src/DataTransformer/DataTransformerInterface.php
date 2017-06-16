<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFrameworkBundle\DataTransformer;


interface DataTransformerInterface
{
    /**
     * @param mixed $data
     *
     * @param array $options
     *
     * @return mixed
     */
    public function transform($data, $options = []);
}