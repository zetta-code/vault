<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\InputFilter;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Filter;
use Zend\I18n\Filter\NumberParse;
use Zend\I18n\Validator\IsFloat;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class CredentialFilter extends InputFilter
{
    /**
     * CredentialFilter constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct($entityManager, $name = 'credential', $options = [])
    {
        $credential = new InputFilter();
        $credential->add([
            'name' => 'id',
            'required' => true,
            'filters' => [['name' => Filter\ToInt::class]],
        ]);

        $credential->add([
            'name' => 'name',
            'required' => false,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\ToNull::class],
            ],
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 256,
                    ],
                ],
            ],
        ]);

        $credential->add([
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 256,
                    ],
                ],
            ],
        ]);

        $credential->add([
            'name' => 'value',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 256,
                    ],
                ],
            ],
        ]);

        $credential->add([
            'name' => 'account',
            'required' => false,
            'filters' => [
                ['name' => Filter\ToNull::class],
            ],
        ]);

        $credential->add([
            'name' => 'section',
            'required' => false,
            'filters' => [
                ['name' => Filter\ToNull::class],
            ],
        ]);

        $credential->add([
            'name' => 'description',
            'required' => false,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\ToNull::class],
            ],
            'validators' => [],
        ]);

        $this->add($credential, $name);
    }
}
