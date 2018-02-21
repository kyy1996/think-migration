<?php

namespace think\migration\db;

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\MysqlAdapter;

class Column extends \Phinx\Db\Table\Column
{
    protected $unique = false;

    public function setNullable()
    {
        return $this->setNull(true);
    }

    public function setUnsigned()
    {
        return $this->setSigned(false);
    }

    public function setUnique()
    {
        $this->unique = true;
        return $this;
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function isUnique()
    {
        return $this->getUnique();
    }

    public static function make($name, $type, $options = [])
    {
        $column = new static();
        $column->setName($name);
        $column->setType($type);
        $column->setOptions($options);
        return $column;
    }

    public static function bigInteger($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_BIG_INTEGER);
    }

    public static function binary($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_BLOB);
    }

    public static function boolean($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_BOOLEAN);
    }

    public static function char($name, $length = 255)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_CHAR, compact('length'));
    }

    public static function date($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_DATE);
    }

    public static function dateTime($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_DATETIME);
    }

    public static function decimal($name, $precision = 8, $scale = 2)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_DECIMAL, compact('precision', 'scale'));
    }

    public static function enum($name, array $values)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_ENUM, compact('values'));
    }

    public static function float($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_FLOAT);
    }

    public static function integer($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_INTEGER);
    }

    public static function json($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_JSON);
    }

    public static function jsonb($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_JSONB);
    }

    public static function longText($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_TEXT, ['length' => MysqlAdapter::TEXT_LONG]);
    }

    public static function mediumInteger($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_MEDIUM]);
    }

    public static function mediumText($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_TEXT, ['length' => MysqlAdapter::TEXT_MEDIUM]);
    }

    public static function smallInteger($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_SMALL]);
    }

    public static function string($name, $length = 255)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_STRING, compact('length'));
    }

    public static function text($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_TEXT);
    }

    public static function time($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_TIME);
    }

    public static function tinyInteger($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_INTEGER, ['length' => MysqlAdapter::INT_TINY]);
    }

    /**
     * @param $name
     * @return Column
     */
    public static function unsignedInteger($name)
    {
        return static::integer($name)->setUnSigned();
    }

    public static function timestamp($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_TIMESTAMP);
    }

    public static function uuid($name)
    {
        return static::make($name, AdapterInterface::PHINX_TYPE_UUID);
    }
}
