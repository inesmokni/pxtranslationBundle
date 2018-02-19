# pxtranslationBundle
 This bundle is about making an entity translatable and creating forms to store translated data into database.
 It's based on Gedmo Doctrine2 extensions.
 
# Features
  - Updates the database to handle translation
  - Persist and get translated values
  - Configure as many supported langages as you want 
  - Custom form types to render in forms

# Install pxtranslationBundle

1/ Add require to your composer.json and update:

    "translation/px-translation-bundle" : "dev-master"
   
2/ Update your AppKernel.php:

    new translation\pxTranslationBundle\translationpxTranslationBundle()

3/ Update your assets :

    php app/console assets:install

4/ Add the translation custom theme uder Twig in your config.yml :

    twig:
      form_themes:
         - '@translationpxTranslation/Form/translatable_text-prototype.html.twig'
        
5/ Make sure JQuery is included in your base template:

     <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js" >
     </script>
    
6/ Update your entities by this command, you need to make sure that you delete the correspondents getter and setters before executing the command line : 

    php app/console doctrine:translate:entity myBundle:entityName column1,column2
    (exple: php app/console doctrine:translate:entity AppBundle:Book name,description)
   
7/ Update your database:

    php app/console doctrine:schema:update --force 
   
8/ Update your form type:
    
    - text:
      ->add('column', translatableType::class, array("data" => $builder->getData(), "type" => FlagTextType::class))
      (exple: ->add('name', translatableType::class, array("data" => $builder->getData(), "type" => FlagTextType::class)))
    
    - textarea:
       ->add('comumn', translatableType::class, array("data" => $builder->getData(), "type" => FlagTextAreaType::class)) 
       (exple: ->add('description', translatableType::class, array("data" => $builder->getData(), "type" => FlagTextAreaType::class)))

9/ Just submit your form !

 
 
