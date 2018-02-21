<?php

namespace think\migration;

use Phinx\Db\Adapter\AdapterFactory;
use think\Db;
use think\facade\Config;
use think\migration\adapter\InputAdapter;
use think\migration\adapter\OptimizedMySqlAdapter;
use think\migration\adapter\OutputAdapter;

abstract class Command extends \think\console\Command
{
    /**
     * @var \Phinx\Db\Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * 配置指令
     */
    protected function configure()
    {
    }

    public function getAdapter()
    {
        if (isset($this->adapter)) {
            return $this->adapter;
        }

        $options = $this->getDbConfig();

        AdapterFactory::instance()->registerAdapter('mysql', OptimizedMySqlAdapter::class);

        $adapter = AdapterFactory::instance()->getAdapter($options['adapter'], $options);

        // Automatically time the executed commands
        $adapter = AdapterFactory::instance()->getWrapper('timed', $adapter);

        $adapter->setInput(new InputAdapter($this->input));
        $adapter->setOutput(new OutputAdapter($this->output));

        if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
            $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
        }

        $this->adapter = $adapter;

        return $this->getAdapter();
    }

    /**
     * 获取数据库配置
     * @return array
     */
    protected function getDbConfig()
    {
        $config = Db::connect()->getConfig();

        if (0 == $config['deploy']) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'host'         => $config['hostname'],
                'name'         => $config['database'],
                'user'         => $config['username'],
                'pass'         => $config['password'],
                'port'         => $config['hostport'],
                'charset'      => $config['charset'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'host'         => explode(',', $config['hostname'])[0],
                'name'         => explode(',', $config['database'])[0],
                'user'         => explode(',', $config['username'])[0],
                'pass'         => explode(',', $config['password'])[0],
                'port'         => explode(',', $config['hostport'])[0],
                'charset'      => explode(',', $config['charset'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }

        $dbConfig['default_migration_table'] = $this->getConfig('table', $dbConfig['table_prefix'] . 'migrations');
        $dbConfig['version_order']           = 'creation';

        return $dbConfig;
    }

    protected function getConfig($name, $default = null)
    {
        $config = Config::pull('migration');
        return isset($config[$name]) ? $config[$name] : $default;
    }

    /**
     * Verify that the migration directory exists and is writable.
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function verifyMigrationDirectory($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration directory "%s" does not exist',
                $path
            ));
        }

        if (!is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration directory "%s" is not writable',
                $path
            ));
        }
    }

    /**
     * Verify that the seed directory exists and is writable.
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function verifySeedDirectory($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Seed directory "%s" does not exist',
                $path
            ));
        }

        if (!is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'Seed directory "%s" is not writable',
                $path
            ));
        }
    }
}
