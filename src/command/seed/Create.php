<?php

namespace think\migration\command\seed;

use Phinx\Util\Util;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\input\Option;
use think\console\Output;
use think\migration\command\Seed;

class Create extends Seed
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('make:seeder')
             ->setDescription('Create a new database seeder')
             ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the seeder?')
             ->addOption('path', null, Option::VALUE_REQUIRED, 'Specify the path in which to create this seeder')
             ->setHelp(sprintf('%sCreates a new database seeder%s', PHP_EOL, PHP_EOL));
    }

    /**
     * Create the new seeder.
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
            if ($this->output->confirm($this->input, 'Create seeds directory? [y]/n')) {
                mkdir($path, 0755, true);
            }
        }

        $this->verifySeedDirectory($path);

        $path = realpath($path);

        $className = $input->getArgument('name');

        if (!Util::isValidPhinxClassName($className)) {
            throw new \InvalidArgumentException(sprintf('The seed class name "%s" is invalid. Please use CamelCase format', $className));
        }

        // Compute the file path
        $filePath = $path . DIRECTORY_SEPARATOR . $className . '.php';

        if (is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" already exists', basename($filePath)));
        }

        $namespace = null;
        // inject the class names appropriate to this seeder
        $contents = file_get_contents($this->getTemplate());
        $classes  = [
            '$namespaceDefinition' => $namespace !== null ? ('namespace ' . $namespace . ';') : '',
            '$namespace'           => $namespace,
            '$useClassName'        => 'think\migration\Seeder',
            '$className'           => $className,
            '$baseClassName'       => 'Seeder',
        ];
        $contents = strtr($contents, $classes);

        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf('The file "%s" could not be written to', $path));
        }

        $output->writeln('<info>using seed base class</info> ' . $classes['$useClassName']);
        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }

    protected function getTemplate()
    {
        return __DIR__ . '/../stubs/seed.stub';
    }
}
