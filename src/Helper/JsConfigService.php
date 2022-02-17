<?php
declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Helper;

class JsConfigService
{
    const DEFAULT_VAR_NAME = '_config';

    /**
     * Current variable name to use
     *
     * @var string
     */
    protected $currentVariable = self::DEFAULT_VAR_NAME;

    /**
     * Registered variables
     *
     * @var array
     */
    protected $variables = [self::DEFAULT_VAR_NAME];

    /**
     * The final config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Check to avoid generating script wrappers
     *
     * @var bool
     */
    protected $scriptTag = true;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Returns the service instance. If a var name is set, it changes
     * the current variable to the given var before returning the instance.
     *
     * @param string|null $varName
     *
     * @return self
     */
    public function __invoke(string $varName = null): self
    {
        if (null !== $varName) {
            return $this->jsConfig($varName);
        }

        return $this;
    }

    /**
     * Get helper instance scoped to given variable name
     *
     * @param string $varName
     *
     * @return self
     */
    public function jsConfig(string $varName = self::DEFAULT_VAR_NAME): self
    {
        if (!in_array($varName, $this->variables)) {
            $this->variables[] = $varName;
        }

        $this->currentVariable = $varName;

        return $this;
    }

    /**
     * Add a value to the config. If no varName is specified, the current
     * one will be used.
     *
     * @param mixed $key
     * @param mixed $value
     * @param null|string $varName
     */
    public function add($key, $value = '', string $varName = null)
    {
        if (null === $varName) {
            $varName = $this->currentVariable;
        }

        if (is_array($key)) {
            if (array_key_exists($varName, $this->config) && is_array($this->config[$varName])) {
                $this->config[$varName] = array_merge($this->config[$varName], $key);
            } else {
                $this->config[$varName] = $key;
            }
        } else {
            if (!array_key_exists($varName, $this->config)) {
                $this->config[$varName] = [];
            }

            $this->config[$varName][$key] = $value;
        }
    }

    /**
     * @param bool $scriptTag
     */
    public function generateScriptTag(bool $scriptTag): void
    {
        $this->scriptTag = $scriptTag;
    }

    public function __toString(): string
    {
        $config = [];

        if ($this->scriptTag) {
            $config[] = '<script>';
        }

        foreach ($this->variables as $index => $varKey) {
            if (is_array($this->config[$varKey] ?? null) && count($this->config[$varKey]) > 0) {
                $values = $this->config[$varKey];
            } else {
                $values = new \stdClass();
            }

            $config[] = '    var '.$varKey.' = '.json_encode($values).';';
        }

        if ($this->scriptTag) {
            $config[] = '</script>';
        }

        return implode("\n", $config)."\n";
    }
}
