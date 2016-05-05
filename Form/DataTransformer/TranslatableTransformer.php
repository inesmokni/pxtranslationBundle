<?php

namespace translation\pxTranslationBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * TranslatableTransformer
 *
 */
class TranslatableTransformer implements DataTransformerInterface {

    private $om;
    private $entity;
    private $property;
    private $container;

    public function __construct(ObjectManager $om, $builder, $container, $parent_data) {
        $this->om = $om;
        $this->entity = $parent_data;

        $this->property = $builder->getName();
        $this->container = $container;
    }

    // transforms the Issue object to an array
    public function transform($val) {
        if (!$val) {
            return null;
        }
        $values = array();
        if (is_string($val)):
            if ($this->entity):
                foreach ($this->entity->getTranslations() as $translation):

                    if ($translation->getProperty() == $this->property):
                        $method = 'get' . $this->humanize($this->property);
                        $values[$translation->getLocale()] = $translation->getValue();
                    endif;
                endforeach;
            endif;
        else:
            foreach ($val as $value):
                if (is_object($value)):
                    if ($this->property == 'contracts') {
                        $values[$value->getLocale()] = $value->getFileName();
                    } else {
                        $method = 'get' . $this->humanize($this->property);
                        $translatable = $value->getTranslatable()->$method();
                        foreach ($translatable as $translation):
                            if ($translation->getProperty() == $this->property):
                                $values[$translation->getLocale()] = $translation->getValue();
                            endif;
                        endforeach;
                    }
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
