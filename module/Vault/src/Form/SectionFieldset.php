<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Form;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Form\Element\ObjectSelect;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zetta\DoctrineUtil\Hydrator\DoctrineObject;
use Zetta\Vault\Entity\Account;
use Zetta\Vault\Entity\Section;

class SectionFieldset extends Fieldset
{
    /**
     * SectionFieldset constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'section', $options = [])
    {
        parent::__construct($name, $options);

        $hidrator = new DoctrineObject($entityManager);
        $this->setHydrator($hidrator);
        $this->setObject(new Section());

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class,
        ]);

        $this->add([
            'name' => 'name',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Name'),
            ],
            'options' => [
                'label' => _('Name'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type' => Element\Textarea::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Description'),
            ],
            'options' => [
                'label' => _('Description'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'accounts',
            'type' => ObjectSelect::class,
            'attributes' => [
                'class' => 'form-control selectpicker',
                'title' => _('No account'),
                'data-container' => 'body',
                'data-live-search' => 'true',
                'data-actions-box' => 'true',
                'multiple' => true,
                'required' => false,
            ],
            'options' => [
                'label' => _('Accounts'),
                'use_hidden_element' => true,
                'div' => ['class' => 'form-group'],
                'object_manager' => $entityManager,
                'target_class' => Account::class,
                'property' => 'name',
                'is_method' => true,
                'find_method' => [
                    'name' => 'findBy',
                    'params' => [
                        'criteria' => [
                            'deletedAt' => null
                        ],
                        'orderBy' => ['name' => 'ASC'],
                    ],
                ],
            ],
        ]);
    }
}
