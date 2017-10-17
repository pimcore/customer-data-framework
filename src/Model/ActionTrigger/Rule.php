<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Condition\ConditionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use Pimcore\Cache\Runtime;
use Pimcore\Logger;
use Pimcore\Model\AbstractModel;

class Rule extends AbstractModel
{
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $trigger;

    /**
     * @var string
     */
    private $condition;

    /**
     * @var string
     */
    private $action;

    /**
     * @var int
     */
    private $creationDate;

    /**
     * @var int
     */
    private $modificationDate;

    public function __construct()
    {
        $this->trigger = [];
        $this->condition = [];
        $this->action = [];
    }

    /**
     * @param $id
     *
     * @return Rule
     *
     * @throws \Exception
     */
    public static function getById($id = null)
    {
        if ($id === null) {
            return null;
        }

        $cacheKey = 'cmf_plugin_actiontrigger_rule'.$id;

        try {
            $rule = Runtime::load($cacheKey);
            if (!$rule) {
                throw new \Exception('Action trigger rule in runtime cache is null');
            }
        } catch (\Exception $e) {
            try {
                $rule = new self();
                $rule->getDao()->getById($id);
                Runtime::save($rule, $cacheKey);
            } catch (\Exception $e) {
                Logger::error($e->getMessage());

                return null;
            }
        }

        return $rule;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return TriggerDefinitionInterface[]
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @param TriggerDefinitionInterface[] $trigger
     */
    public function setTrigger(array $trigger = null)
    {
        $this->trigger = $trigger;
    }

    /**
     * @return ConditionDefinitionInterface[] $condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return ActionDefinitionInterface[]
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param int $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
