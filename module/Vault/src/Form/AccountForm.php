<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Form;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Form\Element;
use Zend\Form\Form;
use Zetta\Vault\InputFilter\AccountFilter;

/**
 * Class AccountForm
 */
class AccountForm extends Form
{
    /**
     * AccountForm constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'account', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setAttribute('novalidate', true);
        $inputFilter = new AccountFilter($entityManager, $name, $options);
        $inputFilter->init();
        $this->setInputFilter($inputFilter);

        $accountFieldset = new AccountFieldset($entityManager, $name, $options);
        $accountFieldset->setUseAsBaseFieldset(true);

        $this->add($accountFieldset);

//        $this->add([
//            'name' => 'tags',
//            'type' => Element\Text::class,
//            'attributes' => [
//                'class' => 'form-control selectize-tags',
//                'placeholder' => _('Tags'),
//                'data-tag-class' => 'badge badge-info',
//            ],
//            'options' => [
//                'label' => _('Tags'),
//                'div' => ['class' => 'form-group'],
//            ],
//        ]);

        $this->add([
            'name' => 'submit-btn',
            'type' => Element\Submit::class,
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => _('Submit'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
