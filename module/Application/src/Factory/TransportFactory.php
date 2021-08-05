<?php
/**
 * @link      http://github.com/zetta-code/zend-skeleton-application for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\ServiceManager\Factory\FactoryInterface;

class TransportFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        switch ($config['mail']['options']['sender']) {
            case 'smtp':
                $smtpOptions = new SmtpOptions($config['mail']['transport']['options']);
                $transport = new Smtp($smtpOptions);
                break;
            case 'sendmail':
            default:
                $transport = new Sendmail();
        }

        return $transport;
    }
}