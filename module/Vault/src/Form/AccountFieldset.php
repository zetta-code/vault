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
use Zetta\Vault\Entity\Tag;

class AccountFieldset extends Fieldset
{
    /**
     * AccountFieldset constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'account', $options = [])
    {
        parent::__construct($name, $options);

        $hidrator = new DoctrineObject($entityManager);
        $this->setHydrator($hidrator);
        $this->setObject(new Account());

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
            'name' => 'sections',
            'type' => ObjectSelect::class,
            'attributes' => [
                'class' => 'form-control selectpicker',
                'title' => _('No section'),
                'data-container' => 'body',
                'data-live-search' => 'true',
                'data-actions-box' => 'true',
                'multiple' => true,
                'required' => false,
            ],
            'options' => [
                'label' => _('Sections'),
                'use_hidden_element' => true,
                'div' => ['class' => 'form-group'],
                'object_manager' => $entityManager,
                'target_class' => Section::class,
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

        $this->add([
            'name' => 'tags',
            'type' => ObjectSelect::class,
            'attributes' => [
                'class' => 'form-control selectize-tags',
                'multiple' => true,
                'data-tag-class' => 'badge badge-info',
                'data-tag-placeholder' => _('No tag'),
            ],
            'options' => [
                'label' => _('Tags'),
                'use_hidden_element' => true,
                'div' => ['class' => 'form-group'],
                'object_manager' => $entityManager,
                'target_class' => Tag::class,
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
                'disable_inarray_validator' => true,
            ],
        ]);
    }
}
