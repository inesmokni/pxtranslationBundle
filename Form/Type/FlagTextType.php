<?php

namespace translation\pxTranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * Creates a form field object to show a field value
 *
 */
class FlagTextType extends AbstractType {

    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
    	$this->configureOptions($resolver);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
    	$resolver->setDefaults(array(
    		 'attr' => array(
                'size' => 16,
                'class' => 'span9 form-control'
            	
            ))
    			);
    }

    public function getParent() {
        return TextType::class;
    }

    
    public function getName() {
    	return $this->getBlockPrefix();
    }
    
    public function getBlockPrefix()
    {
    	return 'flag_text';
    }
}

?>
