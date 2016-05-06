<?php

namespace translation\pxTranslationBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * TranslatableTransformer
 * 
 * @author mInes <ines.mokni@proxym-it.com>
 *
 */
class TranslatableTransformer implements DataTransformerInterface {

    private $property;
    private $container;

    public function __construct($container, $builder, $parent_data = null) {
        $this->property = $builder->getName();
        $this->container = $container;
    }

    // transforms the Issue object to an array
    public function transform($val) {
        if (!$val) {
            return null;
        }
        $values = array();
        if (is_object($val)):
        	$translatable = $val->getTranslations();
            foreach ($translatable as $translation):
                 if ($translation->getProperty() == $this->property):
                     $values[$translation->getLocale()] = $translation->getValue();
                 endif;
            endforeach;
        endif;
        
        return $values;
    }

    // transforms the entity id into an actual entity
    public function reverseTransform($val) {
        if (!$val) {
            return null;
        }
        return $val;
    }

    public function humanize($text) {
        return preg_replace('/[_\s]+/', '', ucwords(trim(strtolower(preg_replace('/[_\s]+/', ' ', $text)))));
    }

}

?>
