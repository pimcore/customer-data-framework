<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger;

use CustomerManagementFrameworkBundle\ActionTrigger\Action\ActionDefinitionInterface;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Logger;
use Pimcore\Model\AbstractModel;

/**
 * @method ActionDefinition\Dao getDao()
 * @method void save()
 */
class ActionDefinition extends AbstractModel implements ActionDefinitionInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $ruleId;

    /**
     * @var int
     */
    private $actionDelay;

    /**
     * @var string
     */
    private $implementationClass;

    /**
     * @var array
     */
    private $options;

    /**
     * @var int
     */
    private $creationDate;

    /**
     * @var int
     */
    private $modificationDate;

    /**
     * @param int|null $id
     *
     * @return self|null
     *
     * @throws \Exception
     */
    public static function getById($id)
    {
        if ($id === null) {
            return null;
        }

        $cacheKey = 'cmf_plugin_actiontrigger_action'.$id;

        try {
            $rule = RuntimeCache::load($cacheKey);
            if (!$rule) {
                throw new \Exception('Action trigger action in runtime cache is null');
            }
        } catch (\Exception $e) {
            try {
                $rule = new self();
                $rule->getDao()->getById($id);
                RuntimeCache::save($rule, $cacheKey);
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
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * @param int $ruleId
     */
    public function setRuleId($ruleId)
    {
        $this->ruleId = $ruleId;
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

    /**
     * @return string
     */
    public function getImplementationClass()
    {
        return $this->implementationClass;
    }

    /**
     * @param string $implementationClass
     */
    public function setImplementationClass($implementationClass)
    {
        $this->implementationClass = $implementationClass;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
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

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'ruleId' => $this->getRuleId(),
            'actionDelay' => $this->getActionDelay(),
            'implementationClass' => $this->getImplementationClass(),
            'options' => $this->getOptions(),
            'creationDate' => $this->getCreationDate(),
            'modifictaionDate' => $this->getModificationDate(),
        ];
    }
}
