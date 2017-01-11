<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 16:33
 */

namespace CustomerManagementFramework\ActionTrigger\Action;

interface ActionDefinitionInterface
{

    /**
     * @param $id
     *
     * @return self
     */
    public static function getById($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRuleid();

    /**
     * @param int $ruleId
     *
     * @return void
     */
    public function setRuleid($ruleId);

    /**
     * @return int
     */
    public function getActionDelay();

    /**
     * @param int $actionDelay
     *
     * @return void
     */
    public function setActionDelay($actionDelay);

    /**
     * @return string
     */
    public function getImplementationClass();

    /**
     * @param string $implementationClass
     *
     * @return void
     */
    public function setImplementationClass($implementationClass);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     *
     * @return void
     */
    public function setOptions($options);

    /**
     * @param int $creationDate
     *
     * @return void
     */
    public function setCreationDate($creationDate);

    /**
     * @return int
     */
    public function getCreationDate();

    /**
     * @param int $modificationDate
     *
     * @return void
     */
    public function setModificationDate($modificationDate);

    /**
     * @return int
     */
    public function getModificationDate();

    /**
     * @return array
     */
    public function toArray();
}