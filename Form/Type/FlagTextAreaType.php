<?php

namespace translation\pxTranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Creates a form field object to show a field value
 *
 */
class FlagTextAreaType extends AbstractType {


    public function setDefaultOptions(OptionsResolverInterface $resolver) {
    	$this->configureOptions($resolver);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
    	$resolver->setDefaults(array(
			'attr' => array(
                'size' => 16,
                'class' => 'span9'
            ),
    	));
    }
    
    public function getParent() {
        return TextareaType::class;
    }

    public function getName() {
    	return $this->getBlockPrefix();
    }
    
    public function getBlockPrefix()
    {
    	return 'flag_textarea';
    }
}

?>
