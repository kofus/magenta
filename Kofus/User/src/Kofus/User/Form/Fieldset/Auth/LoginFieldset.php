<?php
namespace Kofus\User\Form\Fieldset\Auth;

use Zend\Form\Fieldset;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;
use Kofus\User\Entity\AuthLoginEntity;

class LoginFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function init()
    {
        $el = new Element\Text('account', array('label' => 'User'));
        $el->setAttribute('readonly', 'readonly');
        $this->add($el);
        
        $el = new Element\Select('encryption', array('label' => 'Encryption'));
        $el->setValueOptions(AuthLoginEntity::$ENCRYPTIONS);
        $this->add($el);
        
        $el = new Element\Text('identity', array(
            'label' => 'Identity'
        ));
        $el->setAttribute('autocomplete', 'off');
        $this->add($el);
        
        $el = new Element\Password('password', array(
            'label' => 'Password'
        ));
        $el->setAttribute('autocomplete', 'off');
        $this->add($el);
        
        $el = new Element\Password('password2', array(
            'label' => 'Repeat password'
        ));
        $el->setAttribute('autocomplete', 'off');
        $this->add($el);
        
        $el = new Element\Checkbox('enabled', array('label' => 'enabled?'));
        $this->add($el);
    }

    public function getInputFilterSpecification()
    {
        return array(
            'encryption' => array(
            	'required' => true
            ),
            'identity' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'password' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            'password2' => array(
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Zend\Filter\StringTrim'
                    )
                )
            ),
            
        )
        ;
    }
}
