# pxtranslationBundle
 This bundle is about making an entity translatable and creating forms to store translated data into database.
 It's base on Gedmo Doctrine2 extensions.
 
# Features
  - Updates the database to handle translation
  - Persist and get translated values
  - Configure as many supported langages as you want 
  - Custom form types to render in forms

# Install pxtranslationBundle

1/ Add require to your composer.json :
   "translation/px-translation-bundle" : "dev-master"
   
2/ Update your AppKernel.php:
   new translation\pxTranslationBundle\translationpxTranslationBundle()

3/ Update your assets :
   php app/console assets:install

4/ Add the translation custom theme uder Twig in your config.yml :
    twig:
      form_themes:
        - 'translationpxTranslationBundle:Form:translatable_text-prototype.html.twig'
        
5/ Make sure JQuery is included in your base template:
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js" >
    </script>
    
6/ Update your entities by this command : 
   php app/console doctrine:translate:entity myBundle:entityName column
   (exple: php app/console doctrine:translate:entity AppBundle:Book description)
   
7/ Update your database:
  php app/console doctrine:schema:update --force 
   
8/ Update your form type:
    
    - text:
      ->add('column', 'translatable_text', array("type" => "flag_text", "data" => $builder->getData() ))
      (exple: ->add('name', 'translatable_text', array("type" => "flag_text", "data" => $builder->getData() )) )
    
    - textarea:
       ->add('column', 'translatable_text', array("type" => "flag_textarea", "data" => $builder->getData() ))
       (exple: ->add('description', 'translatable_text', array("type" => "flag_textarea", "data" => $builder->getData() )) )

9/ Just submit your form !

 
 
