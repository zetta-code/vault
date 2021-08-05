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

class MemberFilter extends InputFilter
{
    /**
     * MemberFilter constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct($entityManager, $name = 'member', $options = [])
    {
        $this->add([
            'name' => 'member',
            'required' => true,
            'filters' => [
                ['name' => Filter\ToNull::class],
            ],
        ]);
    }
}
