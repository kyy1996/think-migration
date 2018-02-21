<?php

namespace think\migration\command\seed;

use Phinx\Seed\SeedInterface;
use think\console\Input;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\migration\command\Seed;

class Run extends Seed
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('db:seed')
             ->setDescription('Run database seeders')
             ->addOption('--seed', '-s', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'What is the name of the seeder?')
             ->setHelp(<<<EOT
The <info>db:seed</info> command runs all available or individual seeders

<info>php console db:seed</info>
<info>php console db:seed -s UserSeeder</info>
<info>php think db:seed -s UserSeeder -s PermissionSeeder -s LogSeeder</info>
<info>php console db:seed -v</info>

EOT
             );
    }

    /**
     * Run database seeders.
     *
     * @param Input  $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $seedSet = $input->getOption('seed');

        // run the seed(ers)
        $start = microtime(true);
        if (empty($seedSet)) {
            // run all the seed(ers)
            $this->seed();
        } else {
            // run seed(ers) specified in a comma-separated list of classes
            foreach ($seedSet as $seed) {
                $this->seed(trim($seed));
            }
        }
        $end = microtime(true);

        $output->writeln('');
        $output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }

    public function seed($seed = null)
    {
        $seeds = $this->getSeeds();

        if (null === $seed) {
            // run all seeders
            foreach ($seeds as $seeder) {
                if (array_key_exists($seeder->getName(), $seeds)) {
                    $this->executeSeed($seeder);
                }
            }
        } else {
            // run only one seeder
            if (array_key_exists($seed, $seeds)) {
                $this->executeSeed($seeds[$seed]);
            } else {
                throw new \InvalidArgumentException(sprintf('The seed class "%s" does not exist', $seed));
            }
        }
    }

    protected function executeSeed(SeedInterface $seed)
    {
        $this->output->writeln('');
        $this->output->writeln(' ==' . ' <info>' . $seed->getName() . ':</info>' . ' <comment>seeding</comment>');

        // Execute the seeder and log the time elapsed.
        $start = microtime(true);
        $seed->setAdapter($this->getAdapter());

        // begin the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->beginTransaction();
        }

        // Run the seeder
        if (method_exists($seed, SeedInterface::RUN)) {
            $seed->run();
        }

        // commit the transaction if the adapter supports it
        if ($this->getAdapter()->hasTransactions()) {
            $this->getAdapter()->commitTransaction();
        }
        $end = microtime(true);

        $this->output->writeln(' ==' . ' <info>' . $seed->getName() . ':</info>' . ' <comment>seeded' . ' ' . sprintf('%.4fs', $end - $start) . '</comment>');
    }
}
