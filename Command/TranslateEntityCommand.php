<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace translation\pxTranslationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use translation\pxTranslationBundle\Generator\TranslationGenerator;

/**
 * Generate entity classes from mapping information
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class TranslateEntityCommand extends DoctrineCommand
{
	
	/**
	 * get a doctrine entity generator
	 *
	 * @return EntityGenerator
	 */
	protected function getEntityGenerator()
	{
		$entityGenerator = new TranslationGenerator();
		$entityGenerator->setGenerateAnnotations(false);
		$entityGenerator->setGenerateStubMethods(true);
		$entityGenerator->setRegenerateEntityIfExists(false);
		$entityGenerator->setUpdateEntityIfExists(true);
		$entityGenerator->setNumSpaces(4);
		$entityGenerator->setAnnotationPrefix('ORM\\');
	
		return $entityGenerator;
	}
	
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:translate:entity')
            ->setAliases(array('doctrine:translate:entity'))
            ->setDescription('Updates entity in order to enable translation')
            ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name')
            ->addArgument('column', InputArgument::REQUIRED, 'A column name')
            ->addOption('no-backup', null, InputOption::VALUE_NONE, 'Do not backup existing entities files.')
            ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

            $name = strtr($input->getArgument('name'), '/', '\\');

            if (false !== $pos = strpos($name, ':')) {
                $name = $this->getContainer()->get('doctrine')->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
            }

            if (class_exists($name)) {
                $output->writeln(sprintf('Generating entity "<info>%s</info>"', $name));
                $metadata = $manager->getClassMetadata($name, null);
            }

        
        $generator = $this->getEntityGenerator();

        $backupExisting = !$input->getOption('no-backup');
        $generator->setBackupExisting($backupExisting);

//         $repoGenerator = new EntityRepositoryGenerator();
        
        $columns = explode(",",$input->getArgument('column'));
        foreach ($metadata->getMetadata() as $m) {
            if ($backupExisting) {
                $basename = substr($m->name, strrpos($m->name, '\\') + 1);
                $output->writeln(sprintf('  > backing up <comment>%s.php</comment> to <comment>%s.php~</comment>', $basename, $basename));
            }
            // Getting the metadata for the entity class once more to get the correct path if the namespace has multiple occurrences
            try {
                $entityMetadata = $manager->getClassMetadata($m->getName(), null);
           
            } catch (\RuntimeException $e) {
                // fall back to the bundle metadata when no entity class could be found
                $entityMetadata = $metadata;
            }

            $output->writeln(sprintf('  > generating <comment>%s</comment>', $m->name));
            
            foreach ($columns as $column){
	            $generator->setField($column);
	            $generator->generateTranslation(array($m), $entityMetadata->getPath());
            }

        }
    }
}
