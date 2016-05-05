<?php

namespace translation\pxTranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use translation\pxTranslationBundle\Form\DataTransformer\TranslatableTransformer;

/**
 * Creates a translatable form field
 *
 */
class translatableType extends AbstractType {

    private $em;
    private $languages_list;
    private $container;

    public function __construct($container) {
       $this->container = $container;
       $this->languages_list = $this->container->getParameter("locale_list");
       $this->em = $this->container->get("doctrine")->getManager();
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
//             $transformer = new TranslatableTransformer($this->om, $builder, $this->container, $this->entity);
//             $builder->addModelTransformer($transformer);
            if ($this->languages_list)
                foreach ($this->languages_list as $key=> $language):
                    $required = $language["required"];
                    $builder->add($key, $options['type'], array('required' => $required, 'attr' => array('class' => $options['type'], 'disabled' => isset($options["attr"]['disabled']) && $options["attr"]['disabled'] == true ? 'disabled' : false )));
                endforeach;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
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
