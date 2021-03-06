<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return $this->getConfig();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
