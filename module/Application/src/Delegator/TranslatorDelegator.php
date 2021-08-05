<?php
/**
 * @link      http://github.com/zetta-code/zend-skeleton-application for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Application\Delegator;

use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\Loader\PhpArray;
use Zend\I18n\Translator\Resources;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;
use Zend\Validator\AbstractValidator;

class TranslatorDelegator implements DelegatorFactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        array $options = null
    )
    {
        $translator = $callback();

        $translator->addTranslationFilePattern(
            PhpArray::class,
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        );
        $translator->addTranslationFilePattern(
            PhpArray::class,
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        );

        AbstractValidator::setDefaultTranslator($translator);
        \Locale::setDefault($translator->getLocale());

        return $translator;
    }
}
