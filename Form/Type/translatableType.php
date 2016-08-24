<?php

namespace translation\pxTranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use translation\pxTranslationBundle\Form\DataTransformer\TranslatableTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Creates a translatable form field
 *
 * @author mInes <ines.mokni@proxym-it.com>
 *
 */
class translatableType extends AbstractType {

    private $languages_list;
    private $container;

    public function __construct($container) {
       $this->container = $container;
       $this->languages_list = $this->container->getParameter("locale_list");
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
            $transformer = new TranslatableTransformer( $this->container, $builder);
            $builder->addModelTransformer($transformer);
            if ($this->languages_list)
                foreach ($this->languages_list as $key=> $language):
                    $required = $language["required"];
                    $builder->add($key, $options['type'], array('required' => $required, 'attr' => array('class' => $options['type'], 'disabled' => isset($options["attr"]['disabled']) && $options["attr"]['disabled'] == true ? 'disabled' : false )));
                endforeach;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
					$this->configureOptions($resolver);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
    	$resolver->setDefaults(array(
    		'data_class' => null,
            'type' => 'text',
            'class' => 'span9',
            'parent_data' => null,
            'allow_add' => true,
            'by_reference' => false,
    	));
    }

    public function getName() {
        return 'translatable_text';
    }

}

?>
