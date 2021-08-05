<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * WithCryptServiceFactory
 **/
class WithCryptServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $crypt = $container->get(Crypt::class);
        return new $requestedName($crypt);
    }
}
