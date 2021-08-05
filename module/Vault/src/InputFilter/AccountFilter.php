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

class AccountFilter extends InputFilter
{
    /**
     * AccountFilter constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct($entityManager, $name = 'account', $options = [])
    {
        $account = new InputFilter();
        $account->add([
            'name' => 'id',
            'required' => true,
            'filters' => [['name' => Filter\ToInt::class]],
        ]);

        $account->add([
            'name' => 'name',
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

        $account->add([
            'name' => 'description',
            'required' => false,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\ToNull::class],
            ],
            'validators' => [],
        ]);

        $account->add([
            'name' => 'sections',
            'required' => false,
            'filters' => [
                ['name' => Filter\ToNull::class],
            ],
        ]);

        $this->add($account, $name);

        $this->add([
            'name' => 'tags',
            'required' => false,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
        ]);
    }
}
