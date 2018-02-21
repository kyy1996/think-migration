<?php

namespace think\migration\command\migrate;

use Phinx\Migration\CreationInterface;
use Phinx\Util\Util;
use think\console\input\Argument as InputArgument;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\migration\command\Migrate;

class Create extends Migrate
{
    /**
     * The name of the interface that any external template creation class is required to implement.
     */
    const CREATION_INTERFACE = '\Phinx\Migration\CreationInterface';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('make:migration')
             ->setDescription('Create a new migration')
             ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the migration?')
             ->setHelp(sprintf('%sCreates a new database migration%s', PHP_EOL, PHP_EOL));


        // An alternative template.
        $this->addOption('template', 't', Option::VALUE_REQUIRED, 'Use an alternative template');

        // A classname to be used to gain access to the template content as well as the ability to
        // have a callback once the migration file has been created.
        $this->addOption('class', 'l', Option::VALUE_REQUIRED, 'Use a class implementing "' . self::CREATION_INTERFACE . '" to generate the template');

        // Allow the migration path to be chosen non-interactively.
        $this->addOption('path', null, Option::VALUE_REQUIRED, 'Specify the path in which to create this migration');
    }

    /**
     * Create the new migration.
     *
     * @param Input  $input
     * @param Output $output
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $path = $this->getPath();

        if (!file_exists($path)) {
            if ($this->output->confirm($this->input, 'Create migrations directory? [y]/n')) {
                mkdir($path, 0755, true);
            }
        }

        $this->verifyMigrationDirectory($path);

        $namespace = null;

        $path      = realpath($path);
        $className = $input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new \InvalidArgumentException(sprintf('The migration class name "%s" is invalid. Please use CamelCase format.', $className));
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new \InvalidArgumentException(sprintf('The migration class name "%s" already exists', $className));
        }

        if (!Util::isUniqueMigrationClassName($className, $path)) {
            throw new \InvalidArgumentException(sprintf(
                'The migration class name "%s" already exists',
                $className
            ));
        }

        // Compute the file path
        $fileName = Util::mapClassNameToFileName($className);
        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" already exists', $filePath));
        }

        // Get the alternative template and static class options from the config, but only allow one of them.
        $defaultAltTemplate       = $this->getTemplate();
        $defaultCreationClassName = '';
        if ($defaultAltTemplate && $defaultCreationClassName) {
            throw new \InvalidArgumentException('Cannot define template:class and template:file at the same time');
        }

        // Get the alternative template and static class options from the command line, but only allow one of them.
        $altTemplate       = $input->getOption('template');
        $creationClassName = $input->getOption('class');
        if ($altTemplate && $creationClassName) {
            throw new \InvalidArgumentException('Cannot use --template and --class at the same time');
        }

        // If no commandline options then use the defaults.
        if (!$altTemplate && !$creationClassName) {
            $altTemplate       = $defaultAltTemplate;
            $creationClassName = $defaultCreationClassName;
        }

        // Verify the alternative template file's existence.
        if ($altTemplate && !is_file($altTemplate)) {
            throw new \InvalidArgumentException(sprintf(
                'The alternative template file "%s" does not exist',
                $altTemplate
            ));
        }

        // Verify that the template creation class (or the aliased class) exists and that it implements the required interface.
        if ($creationClassName) {
            // Supplied class does not exist, is it aliased?
            if (!class_exists($creationClassName)) {
                throw new \InvalidArgumentException(sprintf(
                    'The class "%s" does not exist',
                    $creationClassName
                ));
            }

            // Does the class implement the required interface?
            if (!is_subclass_of($creationClassName, self::CREATION_INTERFACE)) {
                throw new \InvalidArgumentException(sprintf(
                    'The class "%s" does not implement the required interface "%s"',
                    $creationClassName,
                    self::CREATION_INTERFACE
                ));
            }
        }

        // Determine the appropriate mechanism to get the template
        if ($creationClassName) {
            // Get the template from the creation class
            /** @var CreationInterface $creationClass */
            $creationClass = new $creationClassName($input, $output);
            $contents      = $creationClass->getMigrationTemplate();
        } else {
            // Load the alternative template if it is defined.
            $contents = file_get_contents($altTemplate ?: $this->getTemplate());
        }

        // inject the class names appropriate to this migration
        $classes  = [
            '$namespaceDefinition' => $namespace !== null ? ('namespace ' . $namespace . ';') : '',
            '$namespace'           => $namespace,
            '$useClassName'        => $this->getMigrationBaseClassName(false),
            '$className'           => $className,
            '$version'             => Util::getVersionFromFileName($fileName),
            '$baseClassName'       => $this->getMigrationBaseClassName(true),
        ];
        $contents = strtr($contents, $classes);

        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        // Do we need to do the post creation call to the creation class?
        if (isset($creationClass)) {
            $creationClass->postMigrationCreation($filePath, $className, $this->getMigrationBaseClassName());
        }

        $output->writeln('<info>using migration base class</info> ' . $classes['$useClassName']);

        if (!empty($altTemplate)) {
            $output->writeln('<info>using alternative template</info> ' . $altTemplate);
        } else if (!empty($creationClassName)) {
            $output->writeln('<info>using template creation class</info> ' . $creationClassName);
        } else {
            $output->writeln('<info>using default template</info>');
        }

        $output->writeln('<info>created</info> ' . str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $filePath));
    }

    protected function getTemplate()
    {
        return realpath(__DIR__ . '/../stubs/migrate.stub');
    }

    /**
     * Gets the base class name for migrations.
     *
     * @param bool $dropNamespace Return the base migration class name without the namespace.
     * @return string
     */
    protected function getMigrationBaseClassName($dropNamespace = true)
    {
        $className = 'think\migration\Migrator';

        return $dropNamespace ? substr(strrchr($className, '\\'), 1) ?: $className : $className;
    }
}
