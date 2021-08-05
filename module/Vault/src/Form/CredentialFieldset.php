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
use Zetta\Vault\Entity\Credential;
use Zetta\Vault\Entity\Tag;

class CredentialFieldset extends Fieldset
{
    /**
     * CredentialFieldset constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'credential', $options = [])
    {
        parent::__construct($name, $options);

        $hidrator = new DoctrineObject($entityManager);
        $this->setHydrator($hidrator);
        $this->setObject(new Credential());

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
            'name' => 'username',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Username'),
            ],
            'options' => [
                'label' => _('Username'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'value',
            'type' => Element\Password::class,
            'attributes' => [
                'id' => $name . '-value',
                'class' => 'form-control',
                'placeholder' => _('Password'),
            ],
            'options' => [
                'label' => _('Password'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'account',
            'type' => ObjectSelect::class,
            'attributes' => [
                'id' => $name . '-account',
                'class' => 'form-control selectpicker',
                'data-container' => 'body',
                'data-live-search' => 'true',
                'required' => false,
            ],
            'options' => [
                'label' => _('Account'),
                'div' => ['class' => 'form-group'],
                'object_manager' => $entityManager,
                'target_class' => Account::class,
                'property' => 'name',
                'is_method' => true,
                'empty_option' => _('No account'),
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
            'name' => 'section',
            'type' => Element\Select::class,
            'attributes' => [
                'id' => $name . '-section',
                'class' => 'form-control selectpicker',
                'data-container' => 'body',
                'data-live-search' => 'true',
                'required' => false,
            ],
            'options' => [
                'label' => _('Section'),
                'div' => ['class' => 'form-group'],
                'empty_option' => _('No section'),
                'disable_inarray_validator' => true,
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
            'name' => 'tags',
            'type' => ObjectSelect::class,
            'attributes' => [
                'class' => 'form-control selectize-tags',
                'multiple' => true,
                'data-tag-class' => 'badge badge-warning',
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
