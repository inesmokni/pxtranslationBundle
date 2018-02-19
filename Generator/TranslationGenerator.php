<?php

namespace translation\pxTranslationBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Tools\EntityGenerator;

/**
 * Generic class used to update PHP5 entity in order to enable translation.
 * extends Doctrine\ORM\Tools\EntityGenerator
 *
 * @author mInes <mokni.inees@gmail.com>
 *
 */
class TranslationGenerator extends EntityGenerator
{

    /**
     * @var string
     */
    protected static $classTemplate =
'<?php

<namespace>

use Doctrine\ORM\Mapping as ORM;

<entityAnnotation>
<entityClassName>
{
<entityBody>
}
';
    
    /**
     * @var string
     */
    protected static $classTransTemplate =
'<?php

<namespace>

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translator\Entity\Translation;
use Doctrine\ORM\Mapping\Entity;

<entityAnnotation>
<entityClassName>
{
<entityBody>   
        
}
';

    /**
     * @var string
     */
    protected static $transFieldTemplate =
'/**
 * @ORM\OneToMany(
 *     targetEntity="<transEntity>",
 *     mappedBy="translatable",
 *     cascade={"persist","remove"}
 * )
 */
 protected $translations;
';

    /**
     * @var string
     */
    protected static $transMethodTemplate =
'		
/**
* <description>
*
* @return <variableType>
*/
public function translate($property, $locale = null) {
    if (null == $this->translations)
        $this->translations = $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    if (null === $locale) {
        return $this;
    }
    return new  \Gedmo\Translator\TranslationProxy($this, $locale, array($property), "<transEntity>", $this->translations);
}

/**
 * Add translations
 *
 * @param <transEntity> $translations
 * @return Book
 */
public function addTranslation(<transEntity> $translations)
{
    $this->translations[] = $translations;

    return $this;
}

/**
 * Remove translations
 *
 * @param <transEntity> $translations
 */
public function removeTranslation(<transEntity> $translations)
{
    $this->translations->removeElement($translations);
}

/**
 * Get translations
 *
 * @return \Doctrine\Common\Collections\Collection 
 */
public function getTranslations()
{
    return $this->translations;
}		
        
' ;

    /**
     * @var string
     */
    protected static $getMethodTemplate =
'/**
* <description>
*
* @return <variableType>
*		
*/
 public function <methodName>(){
    if ($this->translate("<fieldName>", \Locale::getDefault())-><methodName>()):
        <spaces>return $this->translate("<fieldName>", \Locale::getDefault())-><methodName>();
    else:
        <spaces>return $this-><fieldName>;
    endif;
        }' ;

    /**
     * @var string
     */
    protected static $setMethodTemplate =
'/**
* <description>
*
* @param <variableType>$<variableName>
* @return <entity>
*/
public function <methodName>(<methodTypeHint>$<variableName><variableDefault>)
{	if (is_array($<variableName>) && !empty($<variableName>)):

        foreach ($<variableName> as $locale => $cont):
            if($cont == null) $cont = "";
            /** optional*/
            if ($locale == "fr")
                $this-><fieldName> = $cont;
            $this->translate("<fieldName>", $locale)-><methodName>($cont);
        endforeach;
    elseif ($<variableName> !== null) :
        $this-><fieldName> = $<variableName>;
    endif;
    return $this;
}';
    
    
    private $field;
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (version_compare(\Doctrine\Common\Version::VERSION, '2.2.0-DEV', '>=')) {
            $this->annotationsPrefix = 'ORM\\';
        }
    }

    /**
     * Generates and writes entity classes for the given array of ClassMetadataInfo instances and column.
     *
     * @param array  $metadatas
     * @param string $outputDirectory
     *
     * @return void
     */
    
    public function generateTranslation(array $metadatas, $outputDirectory)
    {
    	foreach ($metadatas as $metadata) {
    		$this->writeEntityTranslationClass($metadata, $outputDirectory);
    	}
    }
    /**
     * set entity's column to be translated
     * 
     * @param $field
     */
    public function setField($field){
    	$this->field = $field;
    }

    
    /**
     * Generates and writes entity class to disk for the given ClassMetadataInfo instance.
     *
     * @param ClassMetadataInfo $metadata
     * @param string            $outputDirectory
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function writeEntityTranslationClass(ClassMetadataInfo $metadata, $outputDirectory)
    {
    	$path = $outputDirectory . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name) . $this->extension;
    	$path_trans = $outputDirectory . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name . 'Translation') . $this->extension;
    	
    	$dir = dirname($path);
    	$dir_trans = dirname($path_trans);
    
    	if ( ! is_dir($dir)) {
    		mkdir($dir, 0775, true);
    	}
    	
    	if ( ! is_dir($dir_trans)) {
    		mkdir($dir_trans, 0775, true);
    	}
    
    	$this->isNew = !file_exists($path) || (file_exists($path) && $this->regenerateEntityIfExists);
    	$this->isNewTrans = !file_exists($path_trans) || (file_exists($path_trans) && $this->regenerateEntityIfExists);
    
    	if ( ! $this->isNew) {
    		$this->parseTokensInEntityFile(file_get_contents($path));
    	} else {
    		$this->staticReflection[$metadata->name] = array('properties' => array(), 'methods' => array());
    	}
    
    	if ($this->backupExisting && file_exists($path)) {
    		$backupPath = dirname($path) . DIRECTORY_SEPARATOR . basename($path) . "~";
    		if (!copy($path, $backupPath)) {
    			throw new \RuntimeException("Attempt to backup overwritten entity file but copy operation failed.");
    		}
    	}
    	// If entity doesn't exist or we're re-generating the entities entirely
    	if ($this->isNew) {
    		file_put_contents($path, $this->generateEntityClass($metadata));
    		// If entity exists and we're allowed to update the entity class
    	} elseif ( ! $this->isNew && $this->updateEntityIfExists) {
    		file_put_contents($path, $this->generateUpdatedEntityClass($metadata, $path));
    	}
    	
    	/** create the translation entity **/
    	file_put_contents($path_trans, $this->generateEntityTransClass($metadata));
    	
    	
    	chmod($path, 0664);
    	chmod($path_trans, 0664);
    }


    /**
     * Generates the updated code for the given ClassMetadataInfo and entity at path.
     *
     * @param ClassMetadataInfo $metadata
     * @param string            $path
     *
     * @return string
     */
    public function generateUpdatedEntityClass(ClassMetadataInfo $metadata, $path)
    {
        $currentCode = file_get_contents($path);
        $body = $this->generateEntityBody($metadata);
        $transField = $this->generateEntityTransField($metadata);
        $body = str_replace('<spaces>', $this->spaces, $body);
        $first = strrpos($currentCode, '{');
        $last = strrpos(substr($currentCode,$first), '}');

        return substr($currentCode, 0, $first+1)."\n". $transField .  substr($currentCode, $first+1, $last-1) . $body . ($body ? "\n" : '') . "}\n";
    }



    /**
     * Generates a PHP5 Doctrine 2 entity class from the given ClassMetadataInfo instance.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    public function generateEntityClass(ClassMetadataInfo $metadata)
    {
    	$placeHolders = array(
    			'<namespace>',
    			'<useStatement>',
    			'<entityAnnotation>',
    			'<entityClassName>',
    			'<entityBody>'
    	);

    	$replacements = array(
    			$this->generateEntityNamespace($metadata),
    			$this->generateEntityUse(),
    			$this->generateEntityDocBlock($metadata),
    			$this->generateEntityClassName($metadata),
    			$this->generateEntityBody($metadata)
    	);

    	$code = str_replace($placeHolders, $replacements, static::$classTemplate) . "\n";

    	return str_replace('<spaces>', $this->spaces, $code);
    }
    
    protected function generateEntityBody(ClassMetadataInfo $metadata)
    {
    	$stubMethods = $this->generateEntityStubMethods ? $this->generateEntityStubMethods($metadata) : null;
    	$code = array();
    	if ($stubMethods) {
    		$code[] = $stubMethods;
    	}
    	return implode("\n", $code);
    }
    
    protected function generateEntityStubMethods(ClassMetadataInfo $metadata)
    {
    	$methods = array();
        $methods[] = $this->generateEntityTransMethod($metadata);
    	$fieldMapping = $metadata->getFieldMapping($this->field);
    		 
    		if ( ! isset($fieldMapping['id']) || ! $fieldMapping['id'] || $metadata->generatorType == ClassMetadataInfo::GENERATOR_TYPE_NONE) {
    			if ($code = $this->generateEntityStubMethod($metadata, 'set', $fieldMapping['fieldName'], $fieldMapping['type'])) {
    				$methods[] = $code;
    			}
    		}
    
    		if ($code = $this->generateEntityStubMethod($metadata, 'get', $fieldMapping['fieldName'], $fieldMapping['type'])) {
    			$methods[] = $code;
    		}

    	
    	return implode("\n\n", $methods);
    }

    /**
     * Generates a PHP5 Doctrine 2 entity class from the given ClassMetadataInfo instance.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    
    public function generateEntityTransClass(ClassMetadataInfo $metadata)
    {
    	$placeHolders = array(
    			'<namespace>',
    			'<entityAnnotation>',
    			'<entityClassName>',
    			'<entityBody>'
    	);
    
    	$replacements = array(
    			$this->generateEntityNamespace($metadata),
    			$this->generateEntityDocBlock($metadata),
    			$this->generateEntityClassName($metadata),
    			$this->generateEntityTransBody($metadata)
    	);

    	$code = str_replace($placeHolders, $replacements, self::$classTransTemplate);

    	return str_replace('<spaces>', $this->spaces, $code);
    }



    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateEntityNamespace(ClassMetadataInfo $metadata)
    {
        if ($this->hasNamespace($metadata)) {
            return 'namespace ' . $this->getNamespace($metadata) .';';
        }
    }
    
    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateEntityClassName(ClassMetadataInfo $metadata)
    {
    	return 'class ' . $this->getClassName($metadata) . 'Translation' . ' extends Translation';
    }


    
    protected function generateEntityTransBody(ClassMetadataInfo $metadata)
    {
    	$template = '/** @ORM\ManyToOne(targetEntity="<entity>", inversedBy="translations")
     				  * @ORM\JoinColumn(name="translatable_id", referencedColumnName="id")
     				  */
    				protected $translatable;';
    

 		$replacements = array(
 				'<entity>'		  => $metadata->namespace . '\\' . $this->getClassName($metadata),
        );
    	 
    	$code = str_replace(
    			array_keys($replacements),
    			array_values($replacements),
    			$template
    			);
    	
    	return $code;
    }


    /**
     * @param string            $property
     * @param ClassMetadataInfo $metadata
     *
     * @return bool
     */
    protected function hasProperty($property, ClassMetadataInfo $metadata)
    {
        if ($this->extendsClass() || (!$this->isNew && class_exists($metadata->name))) {
            // don't generate property if its already on the base class.
            $reflClass = new \ReflectionClass($this->getClassToExtend() ?: $metadata->name);
            if ($reflClass->hasProperty($property)) {
                return true;
            }
        }

        return (
            isset($this->staticReflection[$metadata->name]) &&
            in_array($property, $this->staticReflection[$metadata->name]['properties'])
        );
    }

    /**
     * @param string            $method
     * @param ClassMetadataInfo $metadata
     *
     * @return bool
     */
    protected function hasMethod($method, ClassMetadataInfo $metadata)
    {
        if ($this->extendsClass() || (!$this->isNew && class_exists($metadata->name))) {
            // don't generate method if its already on the base class.
            $reflClass = new \ReflectionClass($this->getClassToExtend() ?: $metadata->name);

            if ($reflClass->hasMethod($method)) {
                return true;
            }
        }

        return (
            isset($this->staticReflection[$metadata->name]) &&
            in_array($method, $this->staticReflection[$metadata->name]['methods'])
        );
    }

    
    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateEntityDocBlock(ClassMetadataInfo $metadata)
    {
    	$lines = array();
    	$lines[] = '/**';
    	$lines[] = ' * ' . $this->getClassName($metadata) . 'Translation';
    
    		$lines[] = ' *';
    		$lines[] = ' * @ORM\Table(
 			*         name="<entity>_translation",
 			*         indexes={@ORM\Index(name="<entity>_translations_lookup_idx", columns={
 			*             "locale", "translatable_id"
			*         })},
 			*         uniqueConstraints={@ORM\UniqueConstraint(name="<entity>_lookup_unique_idx", columns={
 			*             "locale", "translatable_id", "property"
 			*         })}
 			* )
    	    * @ORM\Entity() 
    		';
    
    	$lines[] = ' */';
    	
    	$replacements = array(
    			'<entity>'		  => strtolower($this->getClassName($metadata)),
    	);
    	
    	$template = implode("\n", $lines);
    	$code = str_replace(
    			array_keys($replacements),
    			array_values($replacements),
    			$template
    			);
    
    	return $code;
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateEntityAssociationMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->associationMappings as $associationMapping) {
            if ($this->hasProperty($associationMapping['fieldName'], $metadata)) {
                continue;
            }

            $lines[] = $this->generateAssociationMappingPropertyDocBlock($associationMapping, $metadata);
            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $associationMapping['fieldName']
                     . ($associationMapping['type'] == 'manyToMany' ? ' = array()' : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    /**
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateEntityFieldMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->fieldMappings as $fieldMapping) {
            if ($this->hasProperty($fieldMapping['fieldName'], $metadata) ||
                $metadata->isInheritedField($fieldMapping['fieldName'])) {
                continue;
            }

            $lines[] = $this->generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $fieldMapping['fieldName']
                     . (isset($fieldMapping['options']['default']) ? ' = ' . var_export($fieldMapping['options']['default'], true) : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    
    protected function generateEntityTransMethod(ClassMetadataInfo $metadata){
    	
    	$methodName = Inflector::singularize("translate");
    	
    	if ($this->hasMethod($methodName, $metadata)) {
    		return '';
    	}
    	
    	$template = self::$transMethodTemplate;  

    	$replacements = array(
    			'<transEntity>'		  => $metadata->namespace . '\\' . $this->getClassName($metadata) . 'Translation',
    	);
    	
    	$method = str_replace(
    			array_keys($replacements),
    			array_values($replacements),
    			$template
    			);
    	
    	return $this->prefixCodeWithSpaces($method);
    	
    }

    protected function generateEntityTransField(ClassMetadataInfo $metadata){

        $methodName = Inflector::singularize("translate");

        if ($this->hasMethod($methodName, $metadata)) {
            return '';
        }

        $template = self::$transFieldTemplate;

        $replacements = array(
            '<transEntity>'		  => $metadata->namespace . '\\' . $this->getClassName($metadata) . 'Translation',
        );

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        return $this->prefixCodeWithSpaces($method);

    }
    
    /**
     * @param ClassMetadataInfo $metadata
     * @param string            $type
     * @param string            $fieldName
     * @param string|null       $typeHint
     * @param string|null       $defaultValue
     *
     * @return string
     */
    protected function generateEntityStubMethod(ClassMetadataInfo $metadata, $type, $fieldName, $typeHint = null,  $defaultValue = null)
    {
        $methodName = $type . Inflector::classify($fieldName);
        if (in_array($type, array("add", "remove"))) {
            $methodName = Inflector::singularize($methodName);
        }

        if ($this->hasMethod($methodName, $metadata)) {
            return '';
        }
        $this->staticReflection[$metadata->name]['methods'][] = $methodName;

        $var = sprintf('%sMethodTemplate', $type);
        
        $template = self::$$var;

        $methodTypeHint = null;
        $types          = Type::getTypesMap();
        $variableType   = $typeHint ? $this->getType($typeHint) . ' ' : null;

        if ($typeHint && ! isset($types[$typeHint])) {
            $variableType   =  '\\' . ltrim($variableType, '\\');
            $methodTypeHint =  '\\' . $typeHint . ' ';
        }

        $replacements = array(
          '<description>'       => ucfirst($type) . ' ' . $fieldName,
          '<methodTypeHint>'    => $methodTypeHint,
          '<variableType>'      => $variableType,
          '<variableName>'      => Inflector::camelize($fieldName),
          '<methodName>'        => $methodName,
          '<fieldName>'         => $fieldName,
          '<variableDefault>'   => ($defaultValue !== null ) ? (' = '.$defaultValue) : '',
          '<entity>'            => $this->getClassName($metadata)
        );

        
        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        return $this->prefixCodeWithSpaces($method);
    }

}
