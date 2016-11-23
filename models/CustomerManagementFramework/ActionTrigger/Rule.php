<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 14:32
 */

namespace CustomerManagementFramework\ActionTrigger;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerInterface;
use Pimcore\Cache\Runtime;
use Pimcore\Model\AbstractModel;

class Rule extends AbstractModel {

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
    private $label;

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
    private $actionDelay;


    /**
     * @param $id
     * @return Rule
     * @throws \Exception
     */
    public static function getById($id = null)
    {
        if ($id === null) {
            return null;
        }

        $cacheKey = "cmf_plugin_actiontrigger_rule" . $id;

        try {
            $rule = Runtime::load($cacheKey);
            if (!$rule) {
                throw new \Exception("Action trigger rule in runtime cache is null");
            }
        } catch (\Exception $e) {
            try {
                $rule = new self();
                $rule->getDao()->getById($id);
                Runtime::save($rule, $cacheKey);
            } catch (\Exception $e) {
                print $e->getMessage();
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
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
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return TriggerInterface[]
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @param TriggerInterface[] $trigger
     */
    public function setTrigger(array $trigger = null)
    {
        $this->trigger = $trigger;
    }

    /**
     * @return string
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
     * @return string
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
    public function getActionDelay()
    {
        return $this->actionDelay;
    }

    /**
     * @param int $actionDelay
     */
    public function setActionDelay($actionDelay)
    {
        $this->actionDelay = $actionDelay;
    }


}