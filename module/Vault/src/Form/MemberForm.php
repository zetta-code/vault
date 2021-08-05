<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Form;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Form\Element\ObjectSelect;
use Zend\Form\Element;
use Zend\Form\Form;
use Zetta\Vault\Entity\Permission;
use Zetta\Vault\InputFilter\MemberFilter;

/**
 * Class MemberForm
 */
class MemberForm extends Form
{
    /**
     * MemberForm constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'member', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setAttribute('novalidate', true);
        $inputFilter = new MemberFilter($entityManager, $name, $options);
        $inputFilter->init();
        $this->setInputFilter($inputFilter);

        $this->add([
            'name' => 'member',
            'type' => Element\Select::class,
            'attributes' => [
                'class' => 'form-control selectpicker',
                'data-container' => 'body',
                'data-live-search' => 'true',
                'required' => false,
            ],
            'options' => [
                'label' => _('Member'),
                'div' => ['class' => 'form-group'],
                'empty_option' => _('No member'),
            ],
        ]);

        $this->add([
            'name' => 'submit-btn',
            'type' => Element\Submit::class,
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => _('Add'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
