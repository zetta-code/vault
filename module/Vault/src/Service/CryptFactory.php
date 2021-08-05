<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * CryptFactory
 **/
class CryptFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $config = $config['crypt'];
        $key    = isset($config['key'])    ? $config['key']    : null;
        $cipher = isset($config['cipher']) ? $config['cipher'] : 'AES-256-CBC';

        if (substr($key, 0, 7) === 'base64:') {
            $key = base64_decode(substr($key, 7));
        }

        return new $requestedName($key, $cipher);
    }
}
