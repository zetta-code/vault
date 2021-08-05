<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\Form;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Form\Element;
use Zend\Form\Form;
use Zetta\Vault\InputFilter\SectionFilter;

/**
 * Class SectionForm
 */
class SectionForm extends Form
{
    /**
     * SectionForm constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'section', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setAttribute('novalidate', true);
        $inputFilter = new SectionFilter($entityManager, $name, $options);
        $inputFilter->init();
        $this->setInputFilter($inputFilter);

        $sectionFieldset = new SectionFieldset($entityManager, $name, $options);
        $sectionFieldset->setUseAsBaseFieldset(true);

        $this->add($sectionFieldset);

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
